# A PHP/MySQL single/average-linkage clustering implementation

All the work is done in `/index/index.php`. This is what builds the cluster hierarchy. In each iteration, the code:

  1. Finds the closest points between pairs of points/clusters
  2. Creates a new set of clusters based on those pairs which are the input for the next iteration
  
It is neither true single-linkage nor true average-linkage: within a single iteration, multiple points can be clustered together, and the nearest-neighbour rule is used. But because new clusters are placed in a centroid position (the average position of the points contained in the cluster), they can later linked using effectively, the average-link distance (centroids are only calculated at the end of each iteration).

As it is, it uses the following table as initial input, and creates a new series of tables to index the data as the clusters are built.

```
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
```

In `/index/index.php`, change `CLUSTER_LEVELS`, the value of `$distance` and the multiplier used to increment `$distance` to customise the number of levels in the cluster and the density of the clusters.

This project contains some other files and experiments, including:

  - A grid-based clustering implementation (`/ajax/markers_cluster1.php`)
  - A single-linkage implementation using code only (`/ajax/markers_cluster2.php`)
  - A view of all this using Google Maps (`/index.php`) which can also be seen in `/images/`
  - A PHP implementation of the Map Kit functions [MKMapPointForCoordinate](https://developer.apple.com/library/ios/documentation/MapKit/Reference/MapKitFunctionsReference/Reference/reference.html#//apple_ref/c/func/MKMapPointForCoordinate) and [MKCoordinateForMapPoint](https://developer.apple.com/library/ios/documentation/MapKit/Reference/MapKitFunctionsReference/Reference/reference.html#//apple_ref/c/func/MKCoordinateForMapPoint) (`/r/maps.php`)

## Resources

For an introduction to clustering algorithms the best I found was this set of videos by [@Victor_Lavrenko](https://twitter.com/Victor_Lavrenko),

  - https://www.youtube.com/watch?v=GVz6Y8r5AkY&list=PLBv09BD7ez_7qIbBhyQDr-LAKWUeycZtx

The book [Algorithms of the Intelligent Web](http://www.amazon.com/Algorithms-Intelligent-Web-Haralambos-Marmanis/dp/1933988665) has a chapter on this and it's not too bad, but it to be so much better. Code from the book is available here:

  - http://www.manning.com/marmanis/
  - https://github.com/rgravina/yooreeka