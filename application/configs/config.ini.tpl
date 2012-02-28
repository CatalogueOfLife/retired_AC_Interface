[production]
; Database
database.params.port     = @DB.PORT@
database.params.host     = @DB.HOST@
database.params.username = @DB.USERNAME@
database.params.password = @DB.PASSWORD@
database.params.dbname   = @DB.NAME@
database.params.charset  = "utf8"

; Interface languages (default = English; on = 1, off = 0)
; To disable the multi-lingual interface, set all languages to 0
language.en = 1                 ; English
language.zh = 1                 ; Chinese
language.pt_BR = 1                 ; Portuguese
language.es = 1                 ; Spanish
language.th = 1                 ; Thai
language.vi = 1                 ; Vietnamese
language.fr = 1                 ; French

; Batch 1 modules (on = 1, off = 0)
module.statistics = 1           ; Show credits and species estimates in taxonomic tree
module.credits = 1              ; Show point of attachment in species details
module.indicators = 1           ; Show indicators for data quality in species and database details
; Display images in species details; if both are enabled images are retrieved from database
module.images_database = 0      ; Retrieve images from database
module.images_ajax = 0          ; Retrieve images from live web service(s)

; Batch 2 modules (on = 1, off = 0)
module.fuzzy_search = 0         ; Enable fuzzy search functionality

module.feedback = 0             ; Enable feedbackservice
; Fill in the location of the feedbackservice
module.feedbackUrl = "/feedbackServiceWrongAdress.txt"

; Advanced settings
; Cookies are used to store display preferences for the taxonomic tree and interface language. 
; Set the cookie expiration time in seconds. The default expiration time is 14 days.
advanced.cookie_expiration = 1209600
; Web services are used to retrieve species images. Set the maximum time in seconds after which
; the application should abort trying to retrieve external data.
advanced.webservice_timeout = 5

; Batch 3 modules (on = 1, off =0)
module.map_search = 0           ; Enables searching by map instead of by text
module.map_species_details = 0  ; Enables map with distribution data on species details page
module.map_browse_tree = 0      ; Enables map with distribution data in the taxonomic tree
module.icons_browse_tree = 0    ; Enables taxonomic rank icons in the taxonomic tree