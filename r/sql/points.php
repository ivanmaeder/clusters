<?php

namespace sql\points;

/*

--2014-07-27

CREATE TABLE IF NOT EXISTS `points` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `coordinate` point NOT NULL,
  `lat` decimal(10,8) NOT NULL,
  `lng` decimal(11,8) NOT NULL,
  `point` point NOT NULL,
  `x` decimal(16,6) NOT NULL,
  `y` decimal(16,6) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

*/

function insert($lat, $lng, $x, $y) {
    $sql = "INSERT INTO points (
              coordinate,
              lat,
              lng,
              point,
              x,
              y
            )
            VALUES (
              GeomFromText('POINT($lng $lat)'),
              $lat,
              $lng,
              GeomFromText('POINT($x $y)'),
              $x,
              $y
            )";

    \db\execute($sql);
}

?>
