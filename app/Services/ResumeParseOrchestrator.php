<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;

class ResumeParseOrchestrator
{
    public function __construct(
        private readonly AffindaResumeParserService $affinda,
        private readonly ResumeNormalizerService $normalizer,
        private readonly ResumeStructureRepairService $repair,
        private readonly ResumeSectionValidatorService $validator,
    ) {}

    /**
     * @return array{
     *   source:string,
     *   standard:array,
     *   builder:array,
     *   message?:string,
     *   meta?:array
     * }
     */
    public function extractFromUpload(UploadedFile $file): array
    {
        $affinda = $this->affinda->parseUploadedFile($file);

        if ($affinda['success'] ?? false) {
            $payload  = $affinda['data'] ?? [];
            $standard = $this->normalizer->fromAffinda($payload);
            $standard = $this->repair->repairAffinda($standard);
            $standard = $this->validator->sanitizeStandard($standard);
            $builder  = $this->normalizer->toBuilderFormat($standard);
            $builder  = $this->validator->sanitizeBuilder($builder);

            return [
                'source'   => 'affinda',
                'standard' => $standard,
                'builder'  => $builder,
                'meta'     => $affinda['meta'] ?? [],
            ];
        }

        return [
            'source'   => 'pending_fallback',
            'standard' => ResumeSchema::empty(),
            'builder'  => [],
            'message'  => $affinda['message'] ?? 'Affinda parsing unavailable.',
        ];
    }
}
