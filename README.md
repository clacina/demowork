# demowork
Sample Code Work

This set of routines matches a search query against an inventory in the database.

Inventory categories include:
Fabrics
Notions
Patterns

The output data is listed in a single table where individual columns mean different things based on the
category in question.

Each category has different fields that can be matched to the search term(s).

Users can search for individual words or all words.  To do this, I employ the
'MATCH(field) AGAINST(term)' SQL paradigm.

The user has the ability to search 1 or all different categories.

The results are returned as an array to a JavaScript JTable object

This can be extended by adding new input 'sections' and new query objects.
