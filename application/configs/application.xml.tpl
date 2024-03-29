<?xml version="1.0" encoding="UTF-8"?>
<config xmlns:zf="http://framework.zend.com/xml/zend-config-xml/1.0/">
    <!-- Production Environment -->
    <production>
        <eti>
            <application>
                <name>Annual Checklist Interface</name>
                <edition>@APP.EDITION@</edition>
                <version>@APP.VERSION@</version>
                <revision>@APP.REVISION@</revision>
                <location>http://www.catalogueoflife.org/annual-checklist/@APP.EDITION@</location>
            </application>
        </eti>
        <phpSettings>
            <display_startup_errors>0</display_startup_errors>
            <display_errors>0</display_errors>
            <error_reporting>E_ALL</error_reporting>
        </phpSettings>
        <includePaths>
            <library><zf:const zf:name="APPLICATION_PATH"/>/../library</library>
        </includePaths>
        <bootstrap>
            <path><zf:const zf:name="APPLICATION_PATH"/>/Bootstrap.php</path>
            <class>Bootstrap</class>
        </bootstrap>
        <database>
            <adapter>Mysqli</adapter>
            <params>
                <port>3306</port>
                <!-- overriden by config.ini -->
                <host></host>
                <username></username>
                <password></password>
                <dbname></dbname>
                <!--  -->
                <profiler enabled="0" />
            </params>
        </database>
        <resources>
            <frontController>
                <controllerDirectory><zf:const zf:name="APPLICATION_PATH"/>/controllers</controllerDirectory>
                <defaultControllerName>search</defaultControllerName>
                <defaultAction>all</defaultAction>
            </frontController>
            <view>
                <encoding>UTF-8</encoding>
                <scripts>/scripts</scripts>
            </view>
        </resources>
        <view>
            <!-- overridden by config.ini -->
            <googleAnalytics>
                <trackerId></trackerId>
            </googleAnalytics>
            <!--  -->
        </view>
        <log enabled="1">
            <filter>
                <priority>3</priority>
            </filter>
        </log>
        <cache enabled="1">
            <directory><zf:const zf:name="APPLICATION_PATH"/>/cache</directory>
            <prefix>aci_cache</prefix>
			<lifetime></lifetime>
        </cache>
    </production>
    <!-- CD Environment -->
    <standalone zf:extends="production">
    </standalone>
    <!-- Staging Environment -->
    <staging zf:extends="production">
    </staging>
    <!-- Testing Environment -->
    <testing zf:extends="production">
        <phpSettings>
            <display_startup_errors>1</display_startup_errors>
            <display_errors>1</display_errors>
            <error_reporting>-1</error_reporting>
        </phpSettings>
        <log enabled="1">
            <filter>
                <priority>6</priority>
            </filter>
        </log>
    </testing>
    <!-- Development Environment -->
    <development zf:extends="production">
        <eti>
            <application>
                <edition>2012</edition>
                <version>1.8</version>
                <revision>@APP.REVISION@</revision>
                <location>http://www.catalogueoflife.org/annual-checklist/2012</location>
            </application>
        </eti>
        <phpSettings>
            <display_startup_errors>1</display_startup_errors>
            <display_errors>1</display_errors>
            <error_reporting>-1</error_reporting>
        </phpSettings>
        <database>
            <params>
                <profiler enabled="1">
                    <class>Zend_Db_Profiler_Firebug</class>
                </profiler>
            </params>
        </database>
        <log enabled="1">
            <filter>
                <priority>7</priority>
            </filter>
        </log>
    </development>
</config>