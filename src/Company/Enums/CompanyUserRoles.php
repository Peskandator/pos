<?php

declare(strict_types=1);

namespace App\Company\Enums;

final class CompanyUserRoles
{
    public const ADMIN = 'admin';
    private const ADMIN_TRANSLATION = 'Admin';
    public const EDTIOR = 'editor';
    private const EDITOR_TRANSLATION = 'Editor';

    public const MEMBER = 'member';
    public const MEMBER_TRANSLATION = 'Člen';

    public static function getAllRoles(): array
    {
        return [
            self::ADMIN,
            self::EDTIOR,
            self::MEMBER,
        ];
    }

    public static function getAllRolesTranslations(): array
    {
        return [
            self::ADMIN_TRANSLATION,
            self::EDITOR_TRANSLATION,
            self::MEMBER_TRANSLATION,
        ];
    }

    public static function getAllRolesWithoutAdmin(): array
    {
        return [
            self::EDTIOR,
            self::MEMBER,
        ];
    }

    public static function getAllRolesWithoutAdminTranslations(): array
    {
        return [
            self::EDITOR_TRANSLATION,
            self::MEMBER_TRANSLATION,
        ];
    }

    public static function getAllRolesWithTransAssoc(): array
    {
        return array_combine(self::getAllRoles(), self::getAllRolesTranslations());
    }

    private function __construct()
    {
        // empty
    }
}
