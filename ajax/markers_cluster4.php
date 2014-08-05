<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/r.php');

require_once('sql/points.php');

/* I have no idea how this is going to look:
 *
 * Using a tiny grid size to begin with, create a cluster with the total
 * number of nodes in each grid. Position the clusters in the average
 * position of each node clustered and record the number of clustered
 * nodes.
 *
 * Then repeat:
 *
 *   1. Expand the cluster size
 *   2. Create a cluster with the total number of nodes, positioning the
 *      cluster at the center of all nodes, giving the original cluster a
 *      weight according to the number of nodes it clustered
 * 
 * The hierarchy of clusters is defined in each iteration.
 */



?>
