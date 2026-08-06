<?php

namespace App\Services;

class RiggedWinService
{
    public function getForcedWinner($inputPrize)
    {
        $riggedRules = [
            'logam mulia @1 gram' => 'Susi Susanti',
            'handphone itel super 26 ultra' => 'Hanindita Basmatulhana',
            'catokan rambut han river' => 'Muhammad Iqbal',
        ];

        $cleanPrize = strtolower(trim($inputPrize));

        foreach ($riggedRules as $keyword => $targetWinnerName) {
            if ($cleanPrize !== '' && str_contains($cleanPrize, $keyword)) {
                return $targetWinnerName; 
            }
        }

        return null;
    }
}