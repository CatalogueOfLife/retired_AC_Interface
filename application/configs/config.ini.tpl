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

; Interface languages (default = English; on = 1, off = 0)
; To disable the multi-lingual interface, set all languages to 0
language.en = 1                 ; English
language.zh = 1                 ; Chinese
language.pt = 1                 ; Portuguese
language.es = 1                 ; Spanish

; Batch 1 modules (on = 1, off = 0)
module.statistics = 1           ; Show credits and species estimates in taxonomic tree
module.credits = 1              ; Show point of attachment in species details
module.indicators = 1           ; Show indicators for data quality in species and database details
module.images = 1               ; Show thumbnails of images in species details