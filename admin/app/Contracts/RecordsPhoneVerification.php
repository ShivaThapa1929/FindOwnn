<?php

namespace App\Contracts;

interface RecordsPhoneVerification
{
    /**
     * @return array{success:bool,message:string,phone:string}
     */
    public function recordExternalVerification(string $phone, string $purpose = 'registration', string $source = 'external'): array;
}
