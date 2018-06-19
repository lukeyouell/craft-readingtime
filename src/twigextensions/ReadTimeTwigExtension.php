<?php
/**
 * Read Time plugin for Craft CMS 3.x
 *
 * Calculate the estimated read time for content.
 *
 * @link      https://github.com/lukeyouell
 * @copyright Copyright (c) 2018 Luke Youell
 */

namespace lukeyouell\readtime\twigextensions;

use lukeyouell\readtime\ReadTime;

use Craft;
use craft\helpers\DateTimeHelper;
use craft\helpers\StringHelper;

use yii\base\ErrorException;

class ReadTimeTwigExtension extends \Twig_Extension
{
    // Public Methods
    // =========================================================================

    public function getName()
    {
        return 'readTime';
    }

    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('readTime', [$this, 'readTimeFunction']),
        ];
    }

    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('readTime', [$this, 'readTimeFilter']),
        ];
    }

    public function readTimeFunction($element, $showSeconds = true)
    {
        $totalSeconds = 0;

        foreach ($element->getFieldLayout()->getFields() as $field) {
            try {
                $value = $element->getFieldValue($field->handle);
                $seconds = $this->valToSeconds($value);

                $totalSeconds = $totalSeconds + $seconds;
            } catch (ErrorException $e) {
                continue;
            }
        }

        $duration = DateTimeHelper::secondsToHumanTimeDuration($seconds, $showSeconds);

        return $duration;
    }

    public function readTimeFilter($value = null, $showSeconds = true)
    {
        $seconds = $this->valToSeconds($value);
        $duration = DateTimeHelper::secondsToHumanTimeDuration($seconds, $showSeconds);

        return $duration;
    }

    // Private Methods
    // =========================================================================

    private function valToSeconds($value)
    {
        $settings = ReadTime::$plugin->getSettings();
        $wpm = $settings->wordsPerMinute;

        $string = StringHelper::toString($value);
        $wordCount = StringHelper::countWords($string);
        $seconds = floor($wordCount / $wpm * 60);

        return $seconds;
    }
}
