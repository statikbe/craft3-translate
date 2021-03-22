<?php

namespace myprojecttests;

use Codeception\Test\Unit;
use statikbe\translate\services\Translate;
use UnitTester;

class TwigExpressionUnitTest extends Unit
{

    public $translator;
    public $expressions;

    /**
     * @var UnitTester
     */
    protected $tester;

    public function _before()
    {
        $this->translator = new Translate();
        $this->expressions = $this->translator->_expressions['twig'];
    }

    public function testSingleQuotes()
    {
        $string = "{{ 'hier'|t }}";
        $result = $this->tester->parseRegex($this->expressions, $string);
        $this->assetMatchesArray($result, ['hier']);

        $string = "{{ 'hier'|raw|t }}";
        $result = $this->tester->parseRegex($this->expressions, $string);
        $this->assetMatchesArray($result, ['hier']);

        $string = "{{ 'hier'|t|raw }}";
        $result = $this->tester->parseRegex($this->expressions, $string);
        $this->assetMatchesArray($result, ['hier']);
    }

    public function testDoubleQuotes()
    {
        $string = '{{ "hier"|t }}';
        $result = $this->tester->parseRegex($this->expressions, $string);
        $this->assetMatchesArray($result, ['hier']);

        $string = '{{ "hier"|raw|t }}';
        $result = $this->tester->parseRegex($this->expressions, $string);
        $this->assetMatchesArray($result, ['hier']);

        $string = '{{ "hier"|t|raw }}';
        $result = $this->tester->parseRegex($this->expressions, $string);
        $this->assetMatchesArray($result, ['hier']);
    }

    public function testNotATranslation()
    {
        $string = '{% set today = "now"|date("Ym") %}';
        $str = $this->tester->parseRegex($this->expressions, $string);
        self::assertEquals([], $str);
    }


    public function testStringWithReturns()
    {
        $string = '{{  "craft
            cms"|t }}';
        $result = $this->tester->parseRegex($this->expressions, $string);
        $this->assetMatchesArray($result, ['craft cms']);

    }

    public function testMultiple()
    {
        $string = '{{ "here"|t }} {{ "there"|t }}';
        $result = $this->tester->parseRegex($this->expressions, $string);
        $this->assetMatchesArray($result, ["here", "there"]);
    }

    public function assetMatchesArray($a, $b)
    {
        // if the indexes don't match, return immediately
        if (count(array_diff_assoc($a, $b))) {
            return [false];
        }
        // we know that the indexes, but maybe not values, match.
        // compare the values between the two arrays
        foreach ($a as $k => $v) {
            if ($v !== $b[$k]) {
                return false;
            }
        }
        // we have identical indexes, and no unequal values
        return true;
    }

}
