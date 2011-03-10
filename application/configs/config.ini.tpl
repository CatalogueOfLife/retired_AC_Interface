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

; Turn modules on with 1, turn off with 0.
; Batch 1 Modules
module.statistics = 1           ;Show credits and species estimates in taxonomic tree
module.credits = 1              ;Show point of attachment in species details
module.indicators = 1           ;Show indicators for data quality in species and database details
module.images = 1               ;Show thumbnails of images in species details