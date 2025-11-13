<?php

namespace Core;

// Basic Emailer class. For production, use a robust library like PHPMailer or Symfony Mailer.
class Emailer {
    /**
     * Sends an email.
     *
     * @param string $to Recipient's email address.
     * @param string $subject Email subject.
     * @param string $message Email body (HTML or plain text).
     * @param string $from Sender's email address.
     * @param string $fromName Sender's name.
     * @return bool True on success, false on failure.
     */
    public static function sendEmail(string $to, string $subject, string $message, ?string $from = null, ?string $fromName = null): bool {
        $finalFrom = $from ?? DEFAULT_EMAIL_FROM;
        $finalFromName = $fromName ?? DEFAULT_EMAIL_FROM_NAME;

        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= 'From: ' . $finalFromName . ' <' . $finalFrom . '>' . "\r\n";
        // $headers .= 'Cc: myboss@example.com' . "\r\n"; // Optional CC
        // $headers .= 'Bcc: mysecretboss@example.com' . "\r\n"; // Optional BCC

        // The mail() function is often unreliable on local dev environments without proper MTA setup.
        // In a real app, using SMTP via PHPMailer is highly recommended.
        if (mail($to, $subject, $message, $headers)) {
            error_log("Email sent to {$to} with subject '{$subject}'.");
            return true;
        } else {
            error_log("Failed to send email to {$to} with subject '{$subject}'. Check mail server configuration.");
            return false;
        }
    }

    /**
     * Placeholder for generating a daily forecast for a user.
     * @param array $user User data (needs at least 'username', potentially 'birth_date' for actual forecast).
     * @return string A personalized forecast message.
     */
    public static function generateUserForecast(array $user): string {
        // This is a placeholder. Real forecast generation would be complex.
        // It might use AstrologyCalculator or NumerologyCalculator with user's birth_date.
        $zodiacSigns = ["Aries", "Taurus", "Gemini", "Cancer", "Leo", "Virgo", "Libra", "Scorpio", "Sagittarius", "Capricorn", "Aquarius", "Pisces"];
        $randomSign = $zodiacSigns[array_rand($zodiacSigns)];

        $forecasts = [
            "Today is a day of new beginnings for {$randomSign}. Embrace change!",
            "Expect good news regarding your finances, {$randomSign}.",
            "Focus on your relationships today, {$randomSign}. Communication is key.",
            "A creative project will bring you joy, {$randomSign}.",
            "Take some time for self-reflection, {$randomSign}. You deserve it."
        ];
        $randomForecast = $forecasts[array_rand($forecasts)];

        $greeting = "Hello " . htmlspecialchars($user['username'] ?? 'Valued User') . ",\n\n";
        $body = "Here is your very generic daily forecast for " . date("l, F jS, Y") . ":\n";
        $body .= $randomForecast . "\n\n";
        $body .= "Remember, these are general insights. For a personalized reading, please visit our website.\n\n";
        $body .= "Best regards,\nThe AstroNumero Team";

        // For HTML email:
        $htmlBody = "<p>" . nl2br($greeting . $body) . "</p>";
        return $htmlBody;
    }
}
?>
