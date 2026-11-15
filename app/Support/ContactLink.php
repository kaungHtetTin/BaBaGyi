<?php

namespace App\Support;

class ContactLink
{
    public static function normalize(string $service, string $contact): ?string
    {
        $service = strtolower(trim($service));
        $contact = trim($contact);
        if ($service === 'telegram') {
            if (preg_match('/^@?([a-z][a-z0-9_]{4,31})$/i', $contact, $match)) {
                return 'https://t.me/'.$match[1];
            }
            if (preg_match('~^(?:https?://)?(?:t\.me|telegram\.me)/([a-z0-9_+/-]+)(?:\?[^\s#]*)?$~i', $contact)) {
                return preg_replace('~^(?:https?://)?(?:t\.me|telegram\.me)/~i', 'https://t.me/', $contact);
            }
        }
        if (in_array($service, ['telegram', 'viber'], true)) {
            $phone = preg_replace('/[\s().-]/', '', $contact);
            if (preg_match('/^\+[1-9][0-9]{6,14}$/', $phone)) {
                return $service === 'telegram' ? 'https://t.me/'.$phone : $phone;
            }
        }
        if ($service === 'viber') {
            if (preg_match('~^https://(?:invite\.viber\.com|vb\.me)/[^\s]+$~i', $contact)) {
                return $contact;
            }
            if (preg_match('~^viber://(?:chat\?number=(?:%2B|\+)[1-9][0-9]{6,14}|pa\?chatURI=[a-z0-9_.%-]+(?:&[^\s]*)?)$~i', $contact)) {
                return $contact;
            }
        }
        return null;
    }

    public static function launch(?string $service, ?string $contact): ?string
    {
        $value = self::normalize((string) $service, (string) $contact);
        if ($value !== null && str_starts_with($value, '+')) {
            return 'viber://chat?number='.rawurlencode($value);
        }
        return $value;
    }
}
