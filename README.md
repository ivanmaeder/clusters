# Notes

## From "Algorithms for the Intelligent Web"

- Hierarchical
   - Agglomerative (bottom-up) 
- Tips:
   - Scan the DB once
   - A good answer should be available at any time (no offline processing)
   - Suspend, stop and resume
   - Incremental update
   - Respect memory limits
   - Forward-only cursor over view of the database
- Dendogram, contains:
   - Proximity threshold
   - Number of clusters
   - Set of clusters
 
```
{
    [0, 5, {{A}, {B}, {C}, {D}, {E}}],
    [1, 3, {{A, B}, {C}, {D, E}}],
    [2, 2, {{A, B, C}, {D, E}}],
    [3, 1, {A, B, C, D, E}]
}

            |
3        ______
        |      |
2      ___     |
      |   |    |
1    __   |   __ 
    |  |  |  |  |
0   A  B  C  D  E

```

- "From an implementation perspective, we capture the structure of the dendrogram with two linked hash maps..."
- Single link O(k n^2)
- Average link O(k n^2)
- MST single link O(k n^2)

## Online
- Grid-based looks crap (like a grid)


## Ideas
 - Maybe it's quicker to use integers instead of floating point numbers for the long/lat values
 - On INSERT maybe we can asynchronously update adjacency matrix (instead of waiting until we need to recluster)
 
## Installation

```
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
```