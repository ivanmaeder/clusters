<?php

/* Expected values obtained through MKMapPointForCoordinate.
 */
class ClusterTest extends \PHPUnit_Framework_TestCase
{
    function testCenter() {
        $actual = \maps\toPoint(0, 0);
        $expected = array('x' => 134217728.000000, 'y' => 134217728.000000);

        $this->assertWithLimitedPrecision($actual, $expected);
    }

    function testTopLeft() {
        $actual = \maps\toPoint(90, -180);
        $expected = array('x' => 0.000000, 'y' => 439674.402484);

        $this->assertWithLimitedPrecision($actual, $expected);
    }

    function testInsideTopLeft() {
        $actual = \maps\toPoint(89, -179);
        $expected = array('x' => 745654.044444, 'y' => 439674.402484);

        $this->assertWithLimitedPrecision($actual, $expected);
    }

    function testInsideTopLeftThreshold() {
        $actual = \maps\toPoint(84, -174);
        $expected = array('x' => 4473924.266667, 'y' => 8240909.780312);

        $this->assertWithLimitedPrecision($actual, $expected);
    }

    function testBottomRight() {
        $actual = \maps\toPoint(-90, 180);
        $expected = array('x' => 268435456.000000, 'y' => 267995781.597516);

        $this->assertWithLimitedPrecision($actual, $expected);
    }

    function testInsideBottomRight() {
        $actual = \maps\toPoint(-89, 179);
        $expected = array('x' => 267689801.955556, 'y' => 267995781.597516);

        $this->assertWithLimitedPrecision($actual, $expected);
    }

    function testInsideBottomRightThreshold() {
        $actual = \maps\toPoint(-84, 174);
        $expected = array('x' => 263961531.733333, 'y' => 260194546.219688);

        $this->assertWithLimitedPrecision($actual, $expected);
    }

    function testNewYork() {
        $actual = \maps\toPoint(40.7127, -74.0059);
        $expected = array('x' => 79034929.352249, 'y' => 100926675.400279);

        $this->assertWithLimitedPrecision($actual, $expected);
    }

    function testBuenosAires() {
        $actual = \maps\toPoint(-34.6033, -58.3817);
        $expected = array('x' => 90685177.273458, 'y' => 161748517.380648);

        $this->assertWithLimitedPrecision($actual, $expected);
    }

    function testTokyo() {
        $actual = \maps\toPoint(35.6800, 139.7700);
        $expected = array('x' => 238437793.792000, 'y' => 105705113.634571);

        $this->assertWithLimitedPrecision($actual, $expected);
    }

    function testReykjavik() {
        $actual = \maps\toPoint(64.1333, -21.9333);
        $expected = array('x' => 117863074.146987, 'y' => 71362694.552850);

        $this->assertWithLimitedPrecision($actual, $expected);
    }

    function testUshuaia() {
        $actual = \maps\toPoint(-54.8000, -68.3000);
        $expected = array('x' => 83289556.764444, 'y' => 183270538.070462);

        $this->assertWithLimitedPrecision($actual, $expected);
    }

    private function assertWithLimitedPrecision($a, $b) {
        $this->assertEquals($this->limitPrecision($a), $this->limitPrecision($b));
    }

    private function limitPrecision($arr) {
        foreach ($arr as $k => &$v) {
            $arr[$k] = round($v, 5);
        }

        return $arr;
    }
}
