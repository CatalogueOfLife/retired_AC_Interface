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

; Interface languages (on = 1, off = 0)
; If a localized version should be used, append the region with _XX (extension in capitals)
; If a single language is selected, the language menu will be hidden.
; To disable the multi-lingual interface, set all languages to 0; 
; in this case the interface will default to the browser language.
; If a language translation file in the browser language is not available, 
; the default language is set to English.
language.en = 1                 ; English
language.zh = 1                 ; Chinese
language.pt_BR = 1              ; Portuguese (Brazil)
language.es = 1                 ; Spanish

; Batch 1 modules (on = 1, off = 0)
module.statistics = 1           ; Show credits and species estimates in taxonomic tree
module.credits = 1              ; Show point of attachment in species details
module.indicators = 1           ; Show indicators for data quality in species and database details
; Display images in species details; if both are enabled image urls are retrieved from database
module.images_database = 0      ; Retrieve images from database
module.images_ajax = 1          ; Retrieve images from live web service(s)

; Advanced settings
; Cookies are used to store display preferences for the taxonomic tree and interface language. 
; Set the cookie expiration time in seconds. The default expiration time is 14 days.
advanced.cookie_expiration = 1209600
; Web services are used to retrieve species images. Set the maximum time in seconds after which
; the application should abort trying to retrieve external data.
advanced.webservice_timeout = 5