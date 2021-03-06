<?php

namespace myprojecttests;

use Codeception\Test\Unit;
use Craft;
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
        $str = $this->tester->parseRegex($this->expressions, $string);
        self::assertEquals('hier', $str);

        $string = "{{ 'hier'|raw|t }}";
        $str = $this->tester->parseRegex($this->expressions, $string);
        self::assertEquals('hier', $str);

        $string = "{{ 'hier'|t|raw }}";
        $str = $this->tester->parseRegex($this->expressions, $string);
        self::assertEquals('hier', $str);
    }

    public function testNotATranslation()
    {
        $string = '{% set today = "now"|date("Ym") %}';
        $str = $this->tester->parseRegex($this->expressions, $string);
        self::assertEquals(false, $str);
    }

        public function testDoubleQuotes()
    {
        $string = '{{ "hier"|t }}';
        $str = $this->tester->parseRegex($this->expressions, $string);
        self::assertEquals('hier', $str);

        $string = '{{ "hier"|raw|t }}';
        $str = $this->tester->parseRegex($this->expressions, $string);
        self::assertEquals('hier', $str);

        $string = '{{ "hier"|t|raw }}';
        $str = $this->tester->parseRegex($this->expressions, $string);
        self::assertEquals('hier', $str);
    }

    public function testStringWithReturns()
    {
        $string = '{{ "Gebruik van pesticiden of andere producten (bvb slakkenkorrels) 
                        tegen ongedierte? (bladluizen, ratten, slakken, mieren, engerlingen, ...)"|t }}';
        $str = $this->tester->parseRegex($this->expressions, $string);
        self::assertEquals("Gebruik van pesticiden of andere producten (bvb slakkenkorrels) tegen ongedierte? (bladluizen, ratten, slakken, mieren, engerlingen, ...)", $str);

        $string = '{{  "craft  
            cms"|t }}';
        $str = $this->tester->parseRegex($this->expressions, $string);
        self::assertEquals('craft cms', $str);
    }

}
