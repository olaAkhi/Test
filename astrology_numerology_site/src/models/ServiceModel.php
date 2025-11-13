<?php

namespace Models;

use PDO;
use PDOException;
use Database;

class ServiceModel {
    private $pdo;

    public function __construct() {
        try {
            $this->pdo = Database::getInstance();
        } catch (PDOException $e) {
            error_log("ServiceModel PDO Connection Error: " . $e->getMessage());
            die("Database connection could not be established in ServiceModel.");
        }
    }

    /**
     * Fetches all active services from the database.
     * @return array An array of service objects or an empty array if none found or error.
     */
    public function getAllActiveServices(): array {
        try {
            $stmt = $this->pdo->query("SELECT id, name, description, price, type, category, input_fields FROM services WHERE is_active = TRUE ORDER BY type, name");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching services: " . $e->getMessage());
            return []; // Return empty array on error
        }
    }

    /**
     * Fetches a single service by its ID.
     * @param int $serviceId
     * @return array|null Service data as an associative array, or null if not found or error.
     */
    public function getServiceById(int $serviceId): ?array {
        try {
            $stmt = $this->pdo->prepare("SELECT id, name, description, price, type, category, input_fields, calculation_logic_ref FROM services WHERE id = :id AND is_active = TRUE");
            $stmt->bindParam(':id', $serviceId, PDO::PARAM_INT);
            $stmt->execute();
            $service = $stmt->fetch(PDO::FETCH_ASSOC);
            return $service ?: null;
        } catch (PDOException $e) {
            error_log("Error fetching service by ID {$serviceId}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Populates the services table with the initial list of services.
     * This is a one-time setup method.
     * It should ideally check if services already exist to prevent duplicates.
     */
    public function populateInitialServices() {
        $initialServices = [
            // Astrology Services
            ['Birth Chart (Natal Chart) Interpretation', 'Full analysis of your personality, strengths, and life path based on your date, time, and place of birth.', 25.00, 'astrology', 'Natal Chart', '["birth_date", "birth_time", "birth_place"]', 'calculateNatalChart'],
            ['Zodiac Sign Personality Reading', 'Detailed traits, strengths, weaknesses, and hidden potentials of your Sun Sign.', 10.00, 'astrology', 'Personality', '["birth_date"]', 'getZodiacSignReading'],
            ['Moon Sign & Emotional Personality Analysis', 'Understand your emotional needs and inner self.', 12.00, 'astrology', 'Personality', '["birth_date", "birth_time"]', 'getMoonSignAnalysis'],
            ['Rising Sign (Ascendant) Interpretation', 'How others see you and your natural first impression.', 12.00, 'astrology', 'Personality', '["birth_date", "birth_time", "birth_place"]', 'getRisingSignInterpretation'],
            ['Love & Relationship Compatibility (Synastry Chart)', 'Compare two birth charts to assess love, friendship, or marriage compatibility.', 30.00, 'astrology', 'Compatibility', '["person1_birth_date", "person1_birth_time", "person1_birth_place", "person2_birth_date", "person2_birth_time", "person2_birth_place"]', 'calculateSynastryChart'],
            ['Composite Relationship Chart (Relationship Blueprint)', 'A combined chart showing the energy of your relationship as a separate entity.', 35.00, 'astrology', 'Compatibility', '["person1_birth_date", "person1_birth_time", "person1_birth_place", "person2_birth_date", "person2_birth_time", "person2_birth_place"]', 'calculateCompositeChart'],
            ['Transit Forecast (Current Planetary Influences)', 'See how the planets are currently affecting your life.', 20.00, 'astrology', 'Forecast', '["birth_date", "birth_time", "birth_place", "forecast_period"]', 'getTransitForecast'],
            ['Solar Return Chart (Birthday Year Prediction)', 'Annual forecast for the year ahead starting from your birthday.', 40.00, 'astrology', 'Forecast', '["birth_date", "birth_time", "birth_place", "return_year"]', 'getSolarReturnChart'],
            ['Career & Money Astrology Reading', 'Explore your ideal career path, financial tendencies, and success patterns.', 25.00, 'astrology', 'Life Path', '["birth_date", "birth_time", "birth_place"]', 'getCareerAstrology'],
            ['Health & Wellness Astrology', 'Reveal areas of physical or emotional vulnerability based on your chart.', 20.00, 'astrology', 'Wellness', '["birth_date", "birth_time", "birth_place"]', 'getHealthAstrology'],
            ['Planetary Hour Guidance', 'Learn how to use the correct planetary hours for success, love, or healing.', 15.00, 'astrology', 'Guidance', '["target_goal", "preferred_date_range"]', 'getPlanetaryHourGuidance'],
            ['New Moon & Full Moon Ritual Timing', 'Personalized advice on manifesting during moon cycles.', 18.00, 'astrology', 'Guidance', '["birth_date", "manifestation_intent"]', 'getMoonRitualTiming'],
            ['Astrological Remedies & Rituals', 'Planet-based solutions for life challenges (crystals, colors, mantras).', 22.00, 'astrology', 'Guidance', '["birth_date", "specific_challenge"]', 'getAstrologicalRemedies'],
            ['Past Life Astrology (Karmic Reading)', 'Clues from your chart about your soul\'s previous incarnations.', 30.00, 'astrology', 'Spiritual', '["birth_date", "birth_time", "birth_place"]', 'getKarmicReading'],
            ['Child’s Astrology Chart (For Parents)', 'Understand your child’s nature, talents, and challenges.', 25.00, 'astrology', 'Family', '["child_birth_date", "child_birth_time", "child_birth_place"]', 'getChildAstrologyChart'],
            ['Spiritual Path & Soul Purpose Reading', 'Discover your deeper soul mission using astrology.', 35.00, 'astrology', 'Spiritual', '["birth_date", "birth_time", "birth_place"]', 'getSoulPurposeReading'],
            ['Planetary Retrograde Impact Reading', 'Find out how Mercury, Venus, or other retrogrades personally affect you.', 18.00, 'astrology', 'Forecast', '["birth_date", "specific_retrograde"]', 'getRetrogradeImpact'],
            ['Astro-Cartography (Location Astrology)', 'Best places to live, travel, or find success based on your birth chart.', 28.00, 'astrology', 'Life Path', '["birth_date", "birth_time", "birth_place"]', 'getAstroCartography'],
            ['Timing for Marriage, Pregnancy, or Major Events', 'Find the most auspicious periods for important life events.', 33.00, 'astrology', 'Guidance', '["birth_date", "event_type"]', 'getEventTiming'],
            ['Complete Destiny Blueprint (Astrology Focused)', 'All-in-one report combining your birth chart, key transits, and soul mission.', 75.00, 'astrology', 'Comprehensive', '["birth_date", "birth_time", "birth_place"]', 'getAstrologyDestinyBlueprint'],

            // Numerology Services
            ['Life Path Number Reading', 'Discover your life’s main purpose and destiny.', 15.00, 'numerology', 'Core Numbers', '["birth_date"]', 'calculateLifePathNumber'],
            ['Destiny (Expression) Number Analysis', 'Understand your natural talents and long-term potential.', 15.00, 'numerology', 'Core Numbers', '["full_name_at_birth"]', 'calculateDestinyNumber'],
            ['Soul Urge (Heart\'s Desire) Number', 'Reveal your innermost desires and motivations.', 15.00, 'numerology', 'Core Numbers', '["full_name_at_birth"]', 'calculateSoulUrgeNumber'],
            ['Personality Number Meaning', 'Understand how others perceive you.', 12.00, 'numerology', 'Core Numbers', '["full_name_at_birth"]', 'calculatePersonalityNumber'],
            ['Birthday Number Interpretation', 'Explore the hidden strengths in your date of birth.', 10.00, 'numerology', 'Core Numbers', '["birth_date"]', 'getBirthdayNumberInterpretation'],
            ['Maturity Number Forecast', 'Predict the qualities you will develop later in life.', 18.00, 'numerology', 'Forecast', '["birth_date", "full_name_at_birth"]', 'getMaturityNumber'],
            ['Personal Year Number Forecast', 'Get guidance on your current year’s energy and focus.', 20.00, 'numerology', 'Forecast', '["birth_date", "current_year"]', 'getPersonalYearNumber'],
            ['Personal Month & Day Numbers', 'Track month-to-month opportunities and challenges.', 15.00, 'numerology', 'Forecast', '["birth_date", "target_month_year"]', 'getPersonalMonthDayNumbers'],
            ['Pinnacle Cycles (Major Life Phases)', 'Discover the four key cycles shaping your life journey.', 25.00, 'numerology', 'Life Path', '["birth_date"]', 'getPinnacleCycles'],
            ['Challenge Numbers (Obstacles to Overcome)', 'Identify recurring life challenges and how to handle them.', 18.00, 'numerology', 'Life Path', '["birth_date"]', 'getChallengeNumbers'],
            ['Karmic Debt Numbers Reading', 'Uncover lessons from past life energy affecting you now.', 20.00, 'numerology', 'Spiritual', '["birth_date", "full_name_at_birth"]', 'getKarmicDebtNumbers'],
            ['Karmic Lesson Numbers', 'Reveal missing qualities or skills you need to develop.', 18.00, 'numerology', 'Spiritual', '["full_name_at_birth"]', 'getKarmicLessonNumbers'],
            ['Hidden Passion Number', 'Find out your strongest inner drive or obsession.', 15.00, 'numerology', 'Personality', '["full_name_at_birth"]', 'getHiddenPassionNumber'],
            ['Balance Number Analysis', 'Understand how to stay emotionally stable under stress.', 15.00, 'numerology', 'Personality', '["full_name_at_birth"]', 'getBalanceNumber'],
            ['Name Numerology (Current Name Vibration)', 'Decode the energy your full name carries.', 20.00, 'numerology', 'Core Numbers', '["current_full_name"]', 'getNameNumerology'],
            ['Business Name Numerology Reading', 'Check if your brand or business name attracts success.', 30.00, 'numerology', 'Business', '["business_name"]', 'getBusinessNameNumerology'],
            ['Lucky Numbers for Life & Success', 'Identify your personal lucky numbers for daily use.', 10.00, 'numerology', 'Guidance', '["birth_date", "full_name_at_birth"]', 'getLuckyNumbers'],
            ['Angel Numbers Interpretation (Seeing 111, 222, etc.)', 'Spiritual meanings behind repeating number patterns.', 10.00, 'numerology', 'Spiritual', '["observed_numbers_sequence"]', 'getAngelNumbersInterpretation'],
            ['Compatibility Reading (Love, Business, Friendship)', 'Numerology-based analysis of compatibility with others.', 25.00, 'numerology', 'Compatibility', '["person1_birth_date", "person1_full_name", "person2_birth_date", "person2_full_name"]', 'getNumerologyCompatibility'],
            ['Full Numerology Chart Blueprint', 'Comprehensive report combining all key personal numbers.', 60.00, 'numerology', 'Comprehensive', '["birth_date", "full_name_at_birth", "current_full_name"]', 'getFullNumerologyBlueprint'],
        ];

        $sql = "INSERT INTO services (name, description, price, type, category, input_fields, calculation_logic_ref, is_active)
                VALUES (:name, :description, :price, :type, :category, :input_fields, :calculation_logic_ref, TRUE)
                ON DUPLICATE KEY UPDATE name=name"; // Simple way to avoid duplicate by name, better to check properly

        try {
            $this->pdo->beginTransaction();
            $stmt = $this->pdo->prepare($sql);
            $count = 0;
            foreach ($initialServices as $service) {
                 // Check if service with this name already exists
                $checkStmt = $this->pdo->prepare("SELECT id FROM services WHERE name = :name");
                $checkStmt->bindParam(':name', $service[0]);
                $checkStmt->execute();
                if (!$checkStmt->fetch()) {
                    $stmt->bindParam(':name', $service[0]);
                    $stmt->bindParam(':description', $service[1]);
                    $stmt->bindParam(':price', $service[2]);
                    $stmt->bindParam(':type', $service[3]);
                    $stmt->bindParam(':category', $service[4]);
                    $stmt->bindParam(':input_fields', $service[5]);
                    $stmt->bindParam(':calculation_logic_ref', $service[6]);
                    $stmt->execute();
                    $count++;
                }
            }
            $this->pdo->commit();
            return $count; // Number of newly inserted services
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            error_log("Error populating initial services: " . $e->getMessage());
            return false;
        }
    }
}
?>
