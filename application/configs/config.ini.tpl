[@ENVIRONMENT@]
; Database
database.params.port     = @DB.PORT@
database.params.host     = @DB.HOST@
database.params.username = @DB.USERNAME@
database.params.password = @DB.PASSWORD@
database.params.dbname   = @DB.NAME@
database.params.charset  = "utf8"

; Google Analytics Tracker Id
view.googleAnalytics.trackerId =

; Batch 1 modules (on = 1, off = 0)
module.statistics = 1           ; Show credits and species estimates in taxonomic tree
module.credits = 1              ; Show point of attachment in species details
module.indicators = 1           ; Show indicators for data quality in species and database details
module.images_ajax = 1          ; Retrieve images from live web service(s)

; Interface languages (on = 1, off = 0)
; If a localized version should be used, append the region with _XX (extension in capitals)
; If a single language is selected, the language menu will be hidden.
; To disable the multi-lingual interface, set all languages to 0; 
; in this case the interface will default to the browser language.
; If a language translation file in the browser language is not available, 
; the default language is set to English.
language.en = 1                 ; English
language.fr = 1                 ; French
language.es = 1                 ; Spanish
language.zh = 1                 ; Chinese
language.pt = 1                 ; Portuguese
language.nl = 1                 ; Dutch
language.th = 1                 ; Thai
language.vi = 1                 ; Vietnamese
; Sort languages (alphabetically = 1; maintain order as above = 0)
language_menu.sort = 1          

; Batch 2 modules (on = 1, off = 0)
module.fuzzy_search = 0         ; Enable fuzzy search functionality

module.feedback = 1				; Enable feedbackservice
; Fill in the location of the feedbackservice
module.feedbackUrl = ""

module.bhl = 1					; Enable BHL reverse lookup service

; Advanced settings
; Cookies are used to store display preferences for the taxonomic tree and interface language. 
; Set the cookie expiration time in seconds. The default expiration time is 14 days.
advanced.cookie_expiration = 1209600
; Web services are used to retrieve species images. Set the maximum time in seconds after which
; the application should abort trying to retrieve external data.
advanced.webservice_timeout = 5

; Batch 3 modules (on = 1, off =0)
module.map_search = 1			; Enables searching by map instead of by text
module.map_species_details = 1	; Enables map with distribution data on species details page
module.map_browse_tree = 1		; Enables map with distribution data in the taxonomic tree
module.icons_browse_tree = 1	; Enables taxonomic rank icons in the taxonomic tree