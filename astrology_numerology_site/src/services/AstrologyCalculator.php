<?php

namespace Services;

use DateTime;
use Exception;

class AstrologyCalculator {

    /**
     * Determines the Zodiac sign based on a birth date.
     *
     * @param string $birthDateString (e.g., 'YYYY-MM-DD')
     * @return array ['sign' => string, 'personality_snippet' => string] or ['error' => string]
     */
    public static function getZodiacSignInfo(string $birthDateString): array {
        try {
            $birthDate = new DateTime($birthDateString);
            $month = (int)$birthDate->format('m');
            $day = (int)$birthDate->format('d');
        } catch (Exception $e) {
            return ['error' => 'Invalid birth date format. Please use YYYY-MM-DD.'];
        }

        $sign = "";
        $personality = "";

        if (($month == 1 && $day >= 20) || ($month == 2 && $day <= 18)) {
            $sign = "Aquarius";
            $personality = "Aquarians are known for their innovative ideas and humanitarian spirit. They are independent and original thinkers.";
        } elseif (($month == 2 && $day >= 19) || ($month == 3 && $day <= 20)) {
            $sign = "Pisces";
            $personality = "Pisceans are compassionate, artistic, and intuitive. They are often gentle and wise.";
        } elseif (($month == 3 && $day >= 21) || ($month == 4 && $day <= 19)) {
            $sign = "Aries";
            $personality = "Aries individuals are energetic, courageous, and confident. They are natural leaders and pioneers.";
        } elseif (($month == 4 && $day >= 20) || ($month == 5 && $day <= 20)) {
            $sign = "Taurus";
            $personality = "Taureans are reliable, patient, and practical. They appreciate security and comfort.";
        } elseif (($month == 5 && $day >= 21) || ($month == 6 && $day <= 20)) {
            $sign = "Gemini";
            $personality = "Geminis are adaptable, outgoing, and intelligent. They are curious and enjoy communication.";
        } elseif (($month == 6 && $day >= 21) || ($month == 7 && $day <= 22)) {
            $sign = "Cancer";
            $personality = "Cancerians are nurturing, emotional, and highly imaginative. They value home and family.";
        } elseif (($month == 7 && $day >= 23) || ($month == 8 && $day <= 22)) {
            $sign = "Leo";
            $personality = "Leos are confident, generous, and creative. They enjoy being the center of attention and are natural leaders.";
        } elseif (($month == 8 && $day >= 23) || ($month == 9 && $day <= 22)) {
            $sign = "Virgo";
            $personality = "Virgos are practical, analytical, and hardworking. They pay attention to detail and are very loyal.";
        } elseif (($month == 9 && $day >= 23) || ($month == 10 && $day <= 22)) {
            $sign = "Libra";
            $personality = "Librans are diplomatic, fair-minded, and sociable. They seek harmony and balance in all things.";
        } elseif (($month == 10 && $day >= 23) || ($month == 11 && $day <= 21)) {
            $sign = "Scorpio";
            $personality = "Scorpios are passionate, resourceful, and brave. They are determined and often mysterious.";
        } elseif (($month == 11 && $day >= 22) || ($month == 12 && $day <= 21)) {
            $sign = "Sagittarius";
            $personality = "Sagittarians are optimistic, adventurous, and humorous. They love freedom and exploration.";
        } elseif (($month == 12 && $day >= 22) || ($month == 1 && $day <= 19)) {
            $sign = "Capricorn";
            $personality = "Capricorns are responsible, disciplined, and self-controlled. They are practical and value tradition.";
        } else {
            return ['error' => 'Could not determine Zodiac sign. Date seems invalid.'];
        }

        return [
            'sign' => $sign,
            'personality_snippet' => $personality,
            'calculation_details' => "Calculated based on birth date: " . $birthDate->format('F d, Y')
        ];
    }

    /**
     * Placeholder for a more complex Natal Chart calculation.
     * @param string $birthDateString (YYYY-MM-DD)
     * @param string $birthTimeString (HH:MM)
     * @param string $birthPlaceString (e.g., "City, Country")
     * @return array Result or error
     */
    public static function calculateNatalChart(string $birthDateString, string $birthTimeString, string $birthPlaceString): array {
        // In a real application, this would involve complex astronomical calculations
        // or calls to an external astrology API (e.g., Swiss Ephemeris based).
        // For now, it's a placeholder.

        // Validate inputs (basic)
        try {
            new DateTime($birthDateString . ' ' . $birthTimeString);
        } catch (Exception $e) {
            return ['error' => 'Invalid birth date or time format.'];
        }
        if (empty($birthPlaceString)) {
            return ['error' => 'Birth place is required.'];
        }

        return [
            'title' => 'Natal Chart Interpretation (Placeholder)',
            'summary' => "This is a placeholder natal chart for $birthDateString, $birthTimeString, at $birthPlaceString.",
            'sun_sign' => self::getZodiacSignInfo($birthDateString)['sign'] ?? 'Unknown',
            'moon_sign_placeholder' => 'Placeholder Moon Sign (e.g., Taurus)',
            'rising_sign_placeholder' => 'Placeholder Rising Sign (e.g., Gemini)',
            'details' => 'Further details about planetary positions and aspects would appear here in a full implementation.'
        ];
    }
}
?>
