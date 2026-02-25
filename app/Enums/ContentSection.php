<?php

namespace App\Enums;

enum ContentSection: string
{
    case INTRODUCTION = 'introduction';
    case MAIN_CONTENT = 'main_content';
    case PRACTICE = 'practice';
    case RESOURCES = 'resources';

    public function label(): string
    {
        return match($this) {
            self::INTRODUCTION => 'Introduction',
            self::MAIN_CONTENT => 'Main Content',
            self::PRACTICE => 'Practice & Exercises',
            self::RESOURCES => 'Resources',
        };
    }

    public function description(): string
    {
        return match($this) {
            self::INTRODUCTION => 'Opening content and overview',
            self::MAIN_CONTENT => 'Primary learning materials',
            self::PRACTICE => 'Interactive activities and assessments',
            self::RESOURCES => 'Additional materials and references',
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::INTRODUCTION => 'fas fa-play-circle',
            self::MAIN_CONTENT => 'fas fa-book-open',
            self::PRACTICE => 'fas fa-dumbbell',
            self::RESOURCES => 'fas fa-folder-open',
        };
    }

    public function order(): int
    {
        return match($this) {
            self::INTRODUCTION => 1,
            self::MAIN_CONTENT => 2,
            self::PRACTICE => 3,
            self::RESOURCES => 4,
        };
    }

    public static function getAllSections(): array
    {
        return collect(self::cases())->map(function ($section) {
            return [
                'value' => $section->value,
                'label' => $section->label(),
                'description' => $section->description(),
                'icon' => $section->icon(),
                'order' => $section->order(),
            ];
        })->sortBy('order')->values()->toArray();
    }
}