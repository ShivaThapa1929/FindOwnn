# Findownn Mobile API Integration Guide

This guide describes how to integrate the Findownn Mobile APIs (v1) into your mobile application (Flutter / React Native / Native Swift & Kotlin).

---

## 1. Authentication & Bearer Tokens

The Findownn Mobile API uses Bearer Token authentication to secure user-specific endpoints (e.g., Booking, Profile, Reviews, and Payments).

### General Flow
1. **Sign Up / Login**: Send a `POST` request to `/auth/register` or `/auth/login`.
2. **Retrieve Token**: On success, the API returns a `201 Created` or `200 OK` containing a `token` string and `expires_at`.
3. **Secure Storage**: Store this token securely in the mobile client:
   * **Flutter**: Use `flutter_secure_storage`.
   * **React Native**: Use `react-native-keychain` or `expo-secure-store`.
4. **Authorize Requests**: For every subsequent request that requires authentication, append the token in the headers:
   ```http
   Authorization: Bearer <your_saved_token>
   ```

---

## 2. API Client Implementations (with Interceptors)

Implement an HTTP client wrapper that automatically attaches the Bearer token if it exists.

### Flutter (Dio Client)

```dart
import 'package:dio/dio.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';

class ApiClient {
  final Dio dio = Dio(BaseOptions(
    baseUrl: 'http://127.0.0.1:8000/api/v1',
    connectTimeout: const Duration(seconds: 10),
    receiveTimeout: const Duration(seconds: 10),
  ));
  
  final _storage = const FlutterSecureStorage();

  ApiClient() {
    dio.interceptors.add(
      InterceptorsWrapper(
        onRequest: (options, handler) async {
          // Retrieve secure token
          final token = await _storage.read(key: 'auth_token');
          if (token != null) {
            options.headers['Authorization'] = 'Bearer $token';
          }
          options.headers['Content-Type'] = 'application/json';
          return handler.next(options);
        },
        onError: (DioException error, handler) {
          if (error.response?.statusCode == 401) {
            // Handle unauthorized status (e.g., trigger logout or token refresh)
            // _storage.delete(key: 'auth_token');
          }
          return handler.next(error);
        },
      ),
    );
  }
}
```

### React Native / Expo (Axios Client)

```javascript
import axios from 'axios';
import * as SecureStore from 'expo-secure-store';

const apiClient = axios.create({
  baseURL: 'http://127.0.0.1:8000/api/v1',
  timeout: 10000,
});

// Request Interceptor
apiClient.interceptors.request.use(
  async (config) => {
    const token = await SecureStore.getItemAsync('auth_token');
    if (token) {
      config.headers['Authorization'] = `Bearer ${token}`;
    }
    config.headers['Content-Type'] = 'application/json';
    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

// Response Interceptor
apiClient.interceptors.response.use(
  (response) => response,
  async (error) => {
    if (error.response && error.response.status === 401) {
      // Trigger navigation to login screen or clear credentials
      await SecureStore.deleteItemAsync('auth_token');
    }
    return Promise.reject(error);
  }
);

export default apiClient;
```

---

## 3. Splash Screen Flow

A premium mobile application requires a splash screen to initialize the application state before displaying the dashboard.

### Recommended Flow on App Launch:
* Check if token exists in local storage.
* If token exists, fetch profile from `/user/profile` to verify session validity.
* If valid, cache credentials and navigate to the Home screen.
* If invalid or empty, redirect the user to the Login screen.

### Flutter Splash Screen Example
```dart
import 'package:flutter/material.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import 'package:go_router/go_router.dart';

class SplashScreen extends StatefulWidget {
  const SplashScreen({Key? key}) : super(key: key);

  @override
  State<SplashScreen> createState() => _SplashScreenState();
}

class _SplashScreenState extends State<SplashScreen> {
  final _storage = const FlutterSecureStorage();

  @override
  void initState() {
    super.initState();
    _checkAuth();
  }

  Future<void> _checkAuth() async {
    // Artificial delay for splash animation
    await Future.delayed(const Duration(milliseconds: 2000));
    
    final token = await _storage.read(key: 'auth_token');
    if (token != null) {
      context.go('/home');
    } else {
      context.go('/login');
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFF080C09), // Brand Dark Green
      body: Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            // Brand Logo
            Image.asset(
              'assets/images/logo.png',
              width: 120,
              height: 120,
            ),
            const SizedBox(height: 24),
            // Brand Wordmark
            const Text(
              'FINDOWNN',
              style: TextStyle(
                color: Colors.white,
                fontSize: 28,
                fontWeight: FontWeight.black,
                letterSpacing: 1.5,
              ),
            ),
            const SizedBox(height: 8),
            // Tagline
            Text(
              'Book courts. Play more.',
              style: TextStyle(
                color: Colors.grey,
                fontSize: 14,
              ),
            ),
          ],
        ),
      ),
    );
  }
}
```

---

## 4. Mobile API Directory & Endpoints

Refer to `Findownn_API_Collection.json` for full Request and Response payloads.

| Section | Method | Endpoint | Description | Auth Required |
| :--- | :--- | :--- | :--- | :--- |
| **Auth** | `POST` | `/auth/register` | Register new player | No |
| **Auth** | `POST` | `/auth/login` | Login and get API token | No |
| **Auth** | `POST` | `/auth/logout` | Invalidate active session token | Yes |
| **Venues** | `GET` | `/venues` | Get filtered list of active venues | No |
| **Venues** | `GET` | `/venues/{id}` | Get details of a single venue | No |
| **Venues** | `GET` | `/venues/{id}/availability` | Check time slots availability for a date | No |
| **Sports** | `GET` | `/sports` | Get list of sports categories | No |
| **Courts** | `GET` | `/courts?venue_id={id}` | Get courts belonging to a venue | No |
| **Bookings** | `POST` | `/bookings` | Create a pending booking | Yes |
| **Bookings** | `GET` | `/bookings` | Retrieve user bookings history | Yes |
| **Bookings** | `POST` | `/bookings/{id}/cancel` | Cancel an upcoming booking | Yes |
| **Payments** | `POST` | `/payments/initiate` | Initiate payment session for a booking | Yes |
| **Payments** | `POST` | `/payments/verify` | Verify payment signature and confirm booking | Yes |
| **User** | `GET` | `/user/profile` | Retrieve profile information | Yes |
| **User** | `PUT` | `/user/profile` | Update profile information | Yes |
| **Reviews** | `POST` | `/reviews` | Submit rating & comments for a venue | Yes |
