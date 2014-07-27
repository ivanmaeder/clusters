<?php

class Map_toCoordinateTest extends \PHPUnit_Framework_TestCase
{
    function testCenter() {
        $actual = \maps\toCoordinate(134217728.000000, 134217728.000000);
        $expected = array('lat' => 0.0, 'lng' => 0.0);

        $this->assertWithLimitedPrecision($actual, $expected);
    }

    function testTopLeft() {
        $actual = \maps\toCoordinate(0, 0);
        $expected = array('lat' => 85.051129, 'lng' => -180.000000);

        $this->assertWithLimitedPrecision($actual, $expected);
    }

    function testInsideTopLeft() {
        $actual = \maps\toCoordinate(0, 1);
        $expected = array('lat' => 85.051129, 'lng' => -179.999999);

        $this->assertWithLimitedPrecision($actual, $expected);
    }

    function testMapKitTopLeft() {
        $actual = \maps\toCoordinate(0.000000, 439674.402484);
        $expected = array('lat' => MAX_MAPKIT_LAT, 'lng' => -180);

        $this->assertWithLimitedPrecision($actual, $expected);
    }

    function testInsideMapKitTopLeft() {
        $actual = \maps\toCoordinate(0.000000, 8240909.780312);
        $expected = array('lat' => MAX_MAPKIT_LAT - 1, 'lng' => -180);

        $this->assertWithLimitedPrecision($actual, $expected);
    }

    function testBottomRight() {
        $actual = \maps\toCoordinate(MAPKIT_MAP_WIDTH, MAPKIT_MAP_HEIGHT);
        $expected = array('lat' => -85.051129, 'lng' => 180);

        $this->assertWithLimitedPrecision($actual, $expected);
    }

    function testMapKitBottomRight() {
        $actual = \maps\toCoordinate(MAPKIT_MAP_WIDTH, 267995781.597516);
        $expected = array('lat' => MIN_MAPKIT_LAT, 'lng' => 180);

        $this->assertWithLimitedPrecision($actual, $expected);
    }

    function testInsideBottomRight() {
        $actual = \maps\toCoordinate(MAPKIT_MAP_WIDTH, MAPKIT_MAP_HEIGHT - 1);
        $expected = array('lat' => -85.051129, 'lng' => 179.999999);

        $this->assertWithLimitedPrecision($actual, $expected);
    }

    function testInsideMapKitBottomRight() {
        $actual = \maps\toCoordinate(MAPKIT_MAP_WIDTH, 260194546.219688);
        $expected = array('lat' => MIN_MAPKIT_LAT + 1, 'lng' => 180);

        $this->assertWithLimitedPrecision($actual, $expected);
    }

    function testNewYork() {
        $actual = \maps\toCoordinate(79034929.352249, 100926675.400279);
        $expected = array('lat' => 40.7127, 'lng' => -74.0059);

        $this->assertWithLimitedPrecision($actual, $expected);
    }

    function testBuenosAires() {
        $actual = \maps\toCoordinate(90685177.273458, 161748517.380648);
        $expected = array('lat' => -34.6033, 'lng' => -58.3817);

        $this->assertWithLimitedPrecision($actual, $expected);
    }

    function testTokyo() {
        $actual = \maps\toCoordinate(238437793.792000, 105705113.634571);
        $expected = array('lat' => 35.6800, 'lng' => 139.7700);

        $this->assertWithLimitedPrecision($actual, $expected);
    }

    function testReykjavik() {
        $actual = \maps\toCoordinate(117863074.146987, 71362694.552850);
        $expected = array('lat' => 64.1333, 'lng' => -21.9333);

        $this->assertWithLimitedPrecision($actual, $expected);
    }

    function testUshuaia() {
        $actual = \maps\toCoordinate(83289556.764444, 183270538.070462);
        $expected = array('lat' => -54.8000, 'lng' => -68.3000);

        $this->assertWithLimitedPrecision($actual, $expected);
    }

    function testErrorX() {
        $actual = \maps\toCoordinate(MAPKIT_MAP_WIDTH + 1, 0);
        $expected = array('lat' => 85.051129, 'lng' => -179.999999);

        $this->assertWithLimitedPrecision($actual, $expected);
    }

    function testY() {
        $actual = \maps\toCoordinate(0, MAPKIT_MAP_HEIGHT + 1);
        $expected = array('lat' => -85.051129, 'lng' => -180.000000);

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
