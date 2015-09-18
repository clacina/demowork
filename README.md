# demowork
Sample Code Work

This set of routines matches a search query against an inventory in the database.

Inventory categories include:
Fabric
Notions
Patterns
Projects

Each category has different fields that can be matched to the search term(s).

Users can search for individual words or all words.  To do this, I employ the
'MATCH(field) AGAINST(term)' SQL paradigm.

The 'queryX' routines build the proper SQL statement to execute the search, including sorting and pagnation.

The user has the ability to search 1 or all different categories.

The results are returned as an array to a JavaScript JTable object

This doesn't represent much in the way of OO design but could easily be migrated to objects.
