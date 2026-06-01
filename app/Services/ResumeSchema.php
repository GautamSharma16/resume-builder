<?php

namespace App\Services;

/**
 * Universal resume schema — single source of truth for parsing output.
 */
final class ResumeSchema
{
    public static function empty(): array
    {
        return [
            'name'           => '',
            'last_name'      => '',
            'designation'    => '',
            'email'          => '',
            'mobile'         => '',
            'location'       => '',
            'linkedin'       => '',
            'github'         => '',
            'website'        => '',
            'summary'        => '',
            'skills'         => [],
            'experience'     => [],
            'education'      => [],
            'projects'       => [],
            'certifications' => [],
            'languages'      => [],
            'achievements'   => [],
        ];
    }
}
