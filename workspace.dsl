workspace "Netfix2 Streaming Platform" "C4 Architecture Model for Netfix2 Online Movie Streaming Platform" {

    model {
        // Actors
        viewer = person "Viewer / User" "A regular or VIP user who browses, streams movies, comments, and subscribes to VIP plans."
        admin = person "Administrator" "An administrative user who manages movies, episodes, user accounts, VIP orders, and views business analytics."

        // Primary Software System
        netfixSystem = softwareSystem "Netfix2 Streaming Platform" "Allows users to watch movies/series, track watch history, comment, and buy VIP packages." {

            // Containers
            webBrowser = container "Web Browser" "Delivers the user interface and video player controls to viewers and admins." "HTML5, CSS3, JavaScript" "WebBrowser"

            webApp = container "Netfix2 Web Application" "Monolithic web application handling HTTP requests, rendering views, business rules, and API endpoints." "PHP 8.2 / Apache (XAMPP)" {
                
                // Components
                authController = component "Auth & Profile Controller" "Handles registration, authentication, session state, and user profiles." "PHP Sessions / MySQLi"
                movieController = component "Movie & Catalog Controller" "Manages movie listings, categories, subgenres, search filters, and detail pages." "PHP / MySQLi"
                streamingController = component "Streaming & Episode Controller" "Delivers video stream sources, checks VIP access permissions, and handles view counters." "PHP / HTML5 Video"
                engagementController = component "Engagement Controller" "Processes user comments and logs watch history records." "PHP / MySQLi"
                paymentController = component "VIP & Payment Controller" "Handles VIP packages, order creation, payment gateway callbacks, and subscription renewals." "PHP / cURL"
                adminController = component "Admin Dashboard Controller" "Provides admin CRUD for movies/episodes, customer management, order approval, and analytics reports." "PHP / MySQLi"
                dbAccess = component "Database Connection (db.php)" "Manages MySQL connection pool and UTF-8 charset configuration." "PHP MySQLi"
            }

            database = container "Netfix2 MySQL Database" "Stores users, movies, episodes, comments, watch history, and VIP orders." "MySQL / MariaDB (netfixvn)" "Database"
            fileStorage = container "Local Media Storage" "Stores uploaded video files (mp4) and movie thumbnail posters." "File System (Uploads/)" "FileStore"
        }

        // External Systems
        momo = softwareSystem "MoMo Payment Gateway" "Processes digital payments via MoMo e-wallet QR code and API." "External"
        vnpay = softwareSystem "VNPay Gateway" "Processes online banking and QR payments via VNPay." "External"

        // Relationships: People -> Containers
        viewer -> webBrowser "Visits website, searches movies, watches streams, and pays for VIP via" "HTTPS"
        admin -> webBrowser "Manages content, users, and orders via admin portal" "HTTPS"
        webBrowser -> webApp "Sends HTTP GET/POST requests to" "HTTP / JSON"

        // Relationships: Web App Components -> Web Browser
        webApp -> webBrowser "Renders HTML pages, CSS, and video playback player to" "HTTP / HTML"

        // Relationships: Components Internal Flow
        authController -> dbAccess "Queries user credentials and updates profiles using"
        movieController -> dbAccess "Queries movie catalog, filters, and metadata using"
        streamingController -> dbAccess "Validates VIP rights and updates view count using"
        engagementController -> dbAccess "Inserts comments and tracks watch history using"
        paymentController -> dbAccess "Creates and updates VIP order status using"
        adminController -> dbAccess "Performs CRUD queries and aggregates statistics using"

        dbAccess -> database "Executes SQL queries and transactions via" "MySQL Protocol / TCP 3306"

        streamingController -> fileStorage "Serves video stream files from" "File I/O"
        adminController -> fileStorage "Uploads thumbnail images and video files to" "File I/O"

        // Relationships: External Systems
        paymentController -> momo "Initiates payment requests and receives IPN/return callbacks from" "HTTPS / REST"
        paymentController -> vnpay "Initiates payment requests and receives return callbacks from" "HTTPS / REST"
        viewer -> momo "Completes payment on" "Mobile App / Web QR"
        viewer -> vnpay "Completes payment on" "Bank Portal / Web QR"
    }

    views {
        systemContext netfixSystem "SystemContext" "C4 Level 1: System Context Diagram for Netfix2" {
            include *
            autoLayout lr
        }

        container netfixSystem "Containers" "C4 Level 2: Container Diagram for Netfix2 Architecture" {
            include *
            autoLayout lr
        }

        component webApp "Components" "C4 Level 3: Component Diagram for Netfix2 Web Application" {
            include *
            autoLayout lr
        }

        styles {
            element "Person" {
                shape Person
                background #08427B
                color #ffffff
            }
            element "Software System" {
                background #1168BD
                color #ffffff
            }
            element "Container" {
                background #438DD5
                color #ffffff
            }
            element "Component" {
                background #85BBF0
                color #000000
            }
            element "Database" {
                shape Cylinder
                background #2A6496
                color #ffffff
            }
            element "FileStore" {
                shape Folder
                background #4B779A
                color #ffffff
            }
            element "WebBrowser" {
                shape WebBrowser
            }
            element "External" {
                background #777777
                color #ffffff
            }
        }
    }
}
