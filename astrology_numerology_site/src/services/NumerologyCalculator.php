<?php

namespace Services;

use DateTime;
use Exception;

class NumerologyCalculator {

    /**
     * Reduces a number to a single digit (or 11, 22, 33 master numbers).
     * @param int $number
     * @param bool $keepMasterNumbers If true, 11, 22, 33 are not reduced further.
     * @return int
     */
    private static function reduceNumber(int $number, bool $keepMasterNumbers = true): int {
        if ($keepMasterNumbers && in_array($number, [11, 22, 33])) {
            return $number;
        }
        $sum = 0;
        while ($number > 0) {
            $sum += $number % 10;
            $number = (int)($number / 10);
        }
        if ($sum > 9 && !($keepMasterNumbers && in_array($sum, [11, 22, 33]))) {
            return self::reduceNumber($sum, $keepMasterNumbers);
        }
        return $sum;
    }

    /**
     * Calculates the Life Path Number from a birth date.
     *
     * @param string $birthDateString (e.g., 'YYYY-MM-DD')
     * @return array ['life_path_number' => int, 'description' => string] or ['error' => string]
     */
    public static function calculateLifePathNumber(string $birthDateString): array {
        try {
            $birthDate = new DateTime($birthDateString);
            $year = (int)$birthDate->format('Y');
            $month = (int)$birthDate->format('m');
            $day = (int)$birthDate->format('d');
        } catch (Exception $e) {
            return ['error' => 'Invalid birth date format. Please use YYYY-MM-DD.'];
        }

        $reducedYear = self::reduceNumber($year);
        $reducedMonth = self::reduceNumber($month);
        $reducedDay = self::reduceNumber($day);

        $lifePathNumber = self::reduceNumber($reducedYear + $reducedMonth + $reducedDay);

        $description = "";
        switch ($lifePathNumber) {
            case 1: $description = "Life Path 1: The Leader. Independent, pioneering, and driven."; break;
            case 2: $description = "Life Path 2: The Diplomat. Cooperative, sensitive, and peacemaking."; break;
            case 3: $description = "Life Path 3: The Communicator. Expressive, creative, and social."; break;
            case 4: $description = "Life Path 4: The Builder. Practical, organized, and hardworking."; break;
            case 5: $description = "Life Path 5: The Adventurer. Freedom-loving, versatile, and dynamic."; break;
            case 6: $description = "Life Path 6: The Nurturer. Responsible, loving, and community-oriented."; break;
            case 7: $description = "Life Path 7: The Seeker. Analytical, intuitive, and introspective."; break;
            case 8: $description = "Life Path 8: The Powerhouse. Ambitious, authoritative, and business-minded."; break;
            case 9: $description = "Life Path 9: The Humanitarian. Compassionate, idealistic, and selfless."; break;
            case 11: $description = "Life Path 11 (Master Number): The Visionary. Intuitive, inspirational, and idealistic."; break;
            case 22: $description = "Life Path 22 (Master Number): The Master Builder. Powerful, practical, and capable of great achievements."; break;
            case 33: $description = "Life Path 33 (Master Number): The Master Teacher. Nurturing, compassionate, and highly influential (rare)."; break;
            default: $description = "Description not available for this number."; break;
        }

        return [
            'life_path_number' => $lifePathNumber,
            'description' => $description,
            'calculation_details' => "Birth Date: {$birthDateString}. Month ({$month}) reduces to {$reducedMonth}. Day ({$day}) reduces to {$reducedDay}. Year ({$year}) reduces to {$reducedYear}. Sum reduces to {$lifePathNumber}."
        ];
    }

    /**
     * Placeholder for calculating Destiny (Expression) Number from full name.
     * @param string $fullNameAtBirth
     * @return array Result or error
     */
    public static function calculateDestinyNumber(string $fullNameAtBirth): array {
        if (empty(trim($fullNameAtBirth))) {
            return ['error' => 'Full name at birth is required.'];
        }
        // This would involve assigning numerical values to letters (Pythagorean or Chaldean system)
        // and summing them up, then reducing.
        return [
            'title' => 'Destiny (Expression) Number (Placeholder)',
            'destiny_number_placeholder' => '5 (Example)', // Example
            'description' => "This is a placeholder for the Destiny Number calculation for '{$fullNameAtBirth}'. It represents your talents and potential.",
            'calculation_method' => 'Based on Pythagorean letter-to-number mapping of your full name at birth.'
        ];
    }
}
?>
