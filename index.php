<?php

/*

 - Hierarchical
    - Agglomerative (bottom-up)
    - 
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

 - "From an implementation perspective, we capture the structure of the dendrogram with two linked hash maps..."


 - Maybe it's quicker to use integers instead of floating point numbers for the long/lat values
 */

?>
