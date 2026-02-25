<?php

namespace App\Enums;

enum ContentVisibility: string
{
    case STUDENT = 'student';
    case TEACHER_ONLY = 'teacher_only';

    public function label(): string
    {
        return match($this) {
            self::STUDENT => 'Visible to Students',
            self::TEACHER_ONLY => 'Teachers Only',
        };
    }

    public function description(): string
    {
        return match($this) {
            self::STUDENT => 'Content visible to both students and teachers',
            self::TEACHER_ONLY => 'Content visible only to teachers and administrators',
        };
    }

    public function badgeClass(): string
    {
        return match($this) {
            self::STUDENT => 'bg-success',
            self::TEACHER_ONLY => 'bg-warning',
        };
    }

    public static function getAllVisibilities(): array
    {
        return collect(self::cases())->map(function ($visibility) {
            return [
                'value' => $visibility->value,
                'label' => $visibility->label(),
                'description' => $visibility->description(),
                'badge_class' => $visibility->badgeClass(),
            ];
        })->toArray();
    }
}