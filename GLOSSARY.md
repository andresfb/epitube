# Epicurus Tube

A self-hosted, single-user video library: a server that catalogues and streams videos, and the native clients that browse and play them.

## Language

### Library

**Content**:
One catalogued item in the library, with its title, tags, category and state.
_Avoid_: Item, entry

**Video**:
The playable media attached to a Content.
_Avoid_: Clip, file, media

**Preview**:
A short, silent excerpt of a Video, shown on a Content card in a Feed.
_Avoid_: Teaser, thumbnail, trailer

**Category**:
A top-level partition of the library; every Feed is scoped to exactly one Category.
_Avoid_: Section, library

**Current Category**:
The Category a client is browsing right now. Each client remembers its own; it starts as the main Category.
_Avoid_: Active category, selected category

**Tag**:
A label on Content, browsable and searchable on its own.
_Avoid_: Keyword, label

### Browsing

**Feed**:
An ordered, paginated list of Content (home, duration, select, tag, search, random).
_Avoid_: List, listing, stream

**Select**:
A Feed curated by the user's own actions, such as liked, featured or watch later.
_Avoid_: Playlist, collection

**Duration**:
A Feed filtered to one length bucket.
_Avoid_: Length filter
