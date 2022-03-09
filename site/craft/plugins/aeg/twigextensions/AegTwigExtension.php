<?php
namespace Craft;

use Twig_Environment;

class AegTwigExtension extends \Twig_Extension {

	public function getName() {
		return 'aeg';
	}

	public function getFilters() {
		return array(
			'truncate' => new \Twig_Filter_Method($this, 'truncate'),
		);
	}

    /**
     * truncate function for words/text (see https://github.com/twigphp/Twig-extensions/blob/master/lib/Twig/Extensions/Extension/Text.php)
     *
     * @param string $value
     * @param int $length
     * @param bool $preserve
     * @param string $separator
     * @return string
     */
	public function truncate($value, $length = 30, $preserve = false, $separator = '...') {
        if (mb_strlen($value, craft()->charset) > $length) {
            if ($preserve) {
                // If breakpoint is on the last word, return the value without separator.
                if (false === ($breakpoint = mb_strpos($value, ' ', $length, craft()->charset))) {
                    return $value;
                }
                $length = $breakpoint;
            }
            return rtrim(mb_substr($value, 0, $length, craft()->charset)).$separator;
        }
        return $value;
	}
}
