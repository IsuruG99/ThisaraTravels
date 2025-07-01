<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Your Journey - Thisara Travels & Tours</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #174038 0%, #2F6DA3 100%);
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* Header */
        .header {
            background: rgba(34, 83, 73, 0.95);
            backdrop-filter: blur(10px);
            padding: 1rem 0;
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .nav-container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 2rem;
        }

        .logo {
            color: #F8F1E9;
            font-size: 1.8rem;
            font-weight: bold;
            text-decoration: none;
        }

        .nav-links {
            display: flex;
            list-style: none;
            gap: 2rem;
            align-items: center;
        }

        .nav-links li {
            position: relative;
        }

        .nav-links a {
            color: #F8F1E9;
            text-decoration: none;
            transition: all 0.3s ease;
            position: relative;
            font-size: 1rem;
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }

        .nav-links a:hover {
            color: #6CC4A1;
            transform: translateY(-2px);
        }

        .nav-links a::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 0;
            height: 2px;
            background: #6CC4A1;
            transition: width 0.3s ease;
        }

        .nav-links a:hover::after {
            width: 100%;
        }

        /* Dropdown Menu */
        .dropdown {
            position: relative;
        }

        .dropdown-menu {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            background: #E6F0EA;
            border: 1px solid #224B41;
            border-radius: 10px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.2);
            min-width: 150px;
            z-index: 1000;
            opacity: 0;
            transform: translateY(10px);
            transition: opacity 0.3s ease, transform 0.3s ease;
        }

        .dropdown:hover .dropdown-menu {
            display: block;
            opacity: 1;
            transform: translateY(0);
        }

        .dropdown-menu li {
            list-style: none;
        }

        .dropdown-menu a {
            color: #224B41;
            padding: 0.8rem 1rem;
            display: block;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        .dropdown-menu a:hover {
            background: #5AB896;
            color: #F8F1E9;
            transform: scale(1.02);
        }

        .dropdown-icon {
            color: #F8F1E9;
            transition: transform 0.3s ease, scale 0.3s ease;
        }

        .dropdown:hover .dropdown-icon {
            transform: rotate(180deg);
            scale: 1.1;
            filter: drop-shadow(0 0 5px #6CC4A1);
        }

        /* Main Container */
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
        }

        .page-title {
            text-align: center;
            color: #F8F1E9;
            margin-bottom: 2rem;
            animation: fadeInDown 1s ease;
        }

        .page-title h1 {
            font-size: 3rem;
            margin-bottom: 0.5rem;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }

        .page-title p {
            font-size: 1.2rem;
            opacity: 0.9;
        }

        /* Intro Section */
        .intro-section {
            background: rgba(230, 240, 234, 0.95);
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            backdrop-filter: blur(10px);
            position: relative;
            display: grid;
            grid-template-columns: 3fr 2fr;
            gap: 1rem;
            align-items: center;
            overflow: hidden;
        }

        .intro-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: -20%;
            width: 60%;
            height: 100%;
            background: linear-gradient(135deg, rgba(34, 83, 73, 0.5), transparent);
            border-radius: 50% 20% / 20% 50%;
            z-index: 0;
        }

        .intro-section .content {
            padding: 1rem;
            color: #224B41;
            position: relative;
            z-index: 1;
        }

        .intro-section h2 {
            font-size: 2rem;
            margin-bottom: 1rem;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.2);
            animation: slideInUp 0.8s ease 0.4s both;
        }

        .intro-section p {
            font-size: 1.1rem;
            line-height: 1.6;
            margin-bottom: 1rem;
            animation: slideInUp 0.8s ease 0.6s both;
        }

        .intro-section .whatsapp-btn {
            animation: slideInUp 0.8s ease 0.8s both;
            position: relative;
            z-index: 1;
        }

        .intro-section .decorative-icon {
            font-size: 8rem;
            color: #2F6DA3;
            opacity: 0.8;
            text-align: center;
            transition: all 0.3s ease;
            animation: fadeIn 0.8s ease 1.0s both;
            z-index: 1;
        }

        .intro-section .decorative-icon:hover {
            transform: scale(1.1);
            filter: drop-shadow(0 0 10px #6CC4A1);
        }

        /* Call-to-Action Section */
        .cta-section {
            background: rgba(230, 240, 234, 0.95);
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            backdrop-filter: blur(10px);
            position: relative;
            display: grid;
            grid-template-columns: 2fr 3fr;
            gap: 1rem;
            align-items: center;
            overflow: hidden;
        }

        .cta-section::before {
            content: '';
            position: absolute;
            top: 0;
            right: -20%;
            width: 60%;
            height: 100%;
            background: linear-gradient(135deg, transparent, rgba(34, 83, 73, 0.5));
            border-radius: 20% 50% / 50% 20%;
            z-index: 0;
        }

        .cta-section .content {
            padding: 1rem;
            color: #224B41;
            position: relative;
            z-index: 1;
        }

        .cta-section h2 {
            font-size: 1.8rem;
            margin-bottom: 1rem;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.2);
            animation: slideInUp 0.8s ease 0.6s both;
        }

        .cta-section p {
            font-size: 1.1rem;
            line-height: 1.6;
            margin-bottom: 1rem;
            animation: slideInUp 0.8s ease 0.8s both;
        }

        .cta-section .whatsapp-btn {
            animation: slideInUp 0.8s ease 1.0s both;
            position: relative;
            z-index: 1;
        }

        .cta-section .decorative-icon {
            font-size: 8rem;
            color: #2F6DA3;
            opacity: 0.8;
            text-align: center;
            transition: all 0.3s ease;
            animation: fadeIn 0.8s ease 1.2s both;
            z-index: 1;
        }

        .cta-section .decorative-icon:hover {
            transform: scale(1.1);
            filter: drop-shadow(0 0 10px #6CC4A1);
        }

        .whatsapp-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 12px 30px;
            background: linear-gradient(135deg, #2F6DA3 0%, #174038 100%);
            color: #F8F1E9;
            border: none;
            border-radius: 25px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        .whatsapp-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(47, 109, 163, 0.3);
        }

        .whatsapp-btn::after {
            content: 'Contact us on WhatsApp';
            position: absolute;
            top: -2.5rem;
            left: 50%;
            transform: translateX(-50%);
            background: #224B41;
            color: #F8F1E9;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            font-size: 0.8rem;
            opacity: 0;
            transition: opacity 0.3s ease;
            pointer-events: none;
        }

        .whatsapp-btn:hover::after {
            opacity: 1;
        }

        /* Filter Section */
        .filter-section {
            background: rgba(230, 240, 234, 0.95);
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            backdrop-filter: blur(10px);
            transform: translateY(0);
            transition: all 0.3s ease;
            animation: slideInUp 0.8s ease;
        }

        .filter-section:hover {
            transform: translateY(-5px);
            box-shadow: 0 25px 50px rgba(0,0,0,0.15);
        }

        .filter-title {
            display: flex;
            align-items: center;
            margin-bottom: 1.5rem;
            color: #224B41;
        }

        .filter-title i {
            margin-right: 0.5rem;
            color: #2F6DA3;
        }

        .filter-grid {
            display: flex;
            flex-direction: row;
            gap: 1.5rem;
            margin-bottom: 1.5rem;
            flex-wrap: nowrap;
        }

        .filter-group {
            position: relative;
            flex: 1;
        }

        .filter-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #555;
        }

        .filter-input, .filter-select {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #E6F0EA;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: #F8F1E9;
        }

        .filter-input:focus, .filter-select:focus {
            outline: none;
            border-color: #2F6DA3;
            transform: scale(1.02);
            box-shadow: 0 0 0 3px rgba(47, 109, 163, 0.1);
        }

        .date-range {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .filter-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
            margin-top: 1rem;
        }

        .btn {
            padding: 12px 30px;
            border: none;
            border-radius: 25px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, #2F6DA3 0%, #174038 100%);
            color: #F8F1E9;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(47, 109, 163, 0.3);
        }

        .btn-secondary {
            background: transparent;
            color: #2F6DA3;
            border: 2px solid #2F6DA3;
        }

        .btn-secondary:hover {
            background: #2F6DA3;
            color: #F8F1E9;
            transform: translateY(-2px);
        }

        /* Vehicles Grid */
        .vehicles-section {
            animation: fadeInUp 1s ease 0.3s both;
        }

        .section-title {
            color: #F8F1E9;
            text-align: center;
            margin-bottom: 2rem;
            font-size: 2rem;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }

        .vehicles-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 2rem;
        }

        .vehicle-card {
            background: rgba(230, 240, 234, 0.95);
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            transform: translateY(0);
            position: relative;
        }

        .vehicle-card:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0 25px 50px rgba(0,0,0,0.2);
        }

        .vehicle-image {
            height: 120px;
            background: linear-gradient(45deg, #2F6DA3, #174038);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #F8F1E9;
            font-size: 2.5rem;
            position: relative;
            overflow: hidden;
        }

        .vehicle-image::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent, rgba(230,240,234,0.1), transparent);
            transform: rotate(45deg);
            transition: all 0.6s ease;
        }

        .vehicle-card:hover .vehicle-image::before {
            animation: shine 0.6s ease;
        }

        .vehicle-info {
            padding: 0.8rem;
        }

        .vehicle-type {
            display: inline-block;
            background: linear-gradient(135deg, #2F6DA3, #174038);
            color: #F8F1E9;
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.6rem;
            font-weight: 600;
            margin-bottom: 0.8rem;
        }

        .vehicle-name {
            font-size: 1.1rem;
            font-weight: bold;
            color: #224B41;
            margin-bottom: 0.4rem;
        }

        .vehicle-features {
            display: flex;
            flex-wrap: wrap;
            gap: 0.8rem;
            margin: 0.8rem 0;
        }

        .feature {
            display: flex;
            align-items: center;
            gap: 0.3rem;
            color: #666;
            font-size: 0.7rem;
        }

        .feature i {
            color: #2F6DA3;
        }

        .vehicle-price {
            font-size: 0.9rem;
            font-weight: bold;
            color: #2F6DA3;
            margin: 0.8rem 0;
        }

        .availability-status {
            display: flex;
            align-items: center;
            gap: 0.4rem;
            margin-bottom: 0.8rem;
            font-size: 0.7rem;
        }

        .status-available {
            color: #28a745;
        }

        .status-unavailable {
            color: #dc3545;
        }

        .book-btn {
            width: 100%;
            padding: 10px;
            background: linear-gradient(135deg, #2F6DA3 0%, #174038 100%);
            color: #F8F1E9;
            border: none;
            border-radius: 10px;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .book-btn:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(47, 109, 163, 0.3);
        }

        .book-btn:disabled {
            background: #ccc;
            cursor: not-allowed;
        }

        /* Loading Animation */
        .loading {
            display: none;
            text-align: center;
            color: #F8F1E9;
            font-size: 1.2rem;
            margin: 2rem 0;
        }

        .spinner {
            display: inline-block;
            width: 40px;
            height: 40px;
            border: 4px solid rgba(230,240,234,0.3);
            border-radius: 50%;
            border-top-color: #F8F1E9;
            animation: spin 1s ease-in-out infinite;
            margin-right: 1rem;
        }

        /* Animations */
        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 0.8;
            }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        @keyframes shine {
            0% {
                transform: translateX(-100%) translateY(-100%) rotate(45deg);
            }
            100% {
                transform: translateX(100%) translateY(100%) rotate(45deg);
            }
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .nav-container {
                padding: 0 1rem;
                flex-direction: column;
                align-items: flex-start;
            }

            .nav-links {
                flex-direction: column;
                gap: 1rem;
                width: 100%;
                margin-top: 1rem;
            }

            .nav-links li {
                width: 100%;
            }

            .dropdown-menu {
                position: static;
                box-shadow: none;
                background: rgba(230, 240, 234, 0.8);
                border: none;
                border-radius: 5px;
            }

            .dropdown:hover .dropdown-menu {
                display: block;
                opacity: 1;
                transform: translateY(0);
            }

            .page-title h1 {
                font-size: 2rem;
            }

            .intro-section, .cta-section {
                padding: 1.5rem;
                display: flex;
                flex-direction: column;
                align-items: center;
            }

            .intro-section .content, .cta-section .content {
                width: 100%;
            }

            .intro-section .decorative-icon, .cta-section .decorative-icon {
                font-size: 5rem;
                margin-bottom: 1rem;
            }

            .intro-section h2, .cta-section h2 {
                font-size: 1.5rem;
            }

            .intro-section p, .cta-section p {
                font-size: 1rem;
            }

            .whatsapp-btn {
                width: 100%;
                justify-content: center;
            }

            .intro-section::before, .cta-section::before {
                display: none;
            }

            .filter-grid {
                flex-direction: column;
                gap: 1rem;
            }

            .date-range {
                grid-template-columns: 1fr;
            }

            .vehicles-grid {
                grid-template-columns: 1fr;
            }

            .filter-buttons {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <nav class="nav-container">
            <a href="#" class="logo">
                <i class="fas fa-car"></i> Thisara Travels
            </a>
            <ul class="nav-links">
                <li><a href="#"><i class="fas fa-home"></i> Home</a></li>
                <li><a href="#"><i class="fas fa-info-circle"></i> About</a></li>
                <li><a href="#"><i class="fas fa-concierge-bell"></i> Services</a></li>
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle">
                        <i class="fas fa-file-alt"></i> Page
                        <i class="fas fa-chevron-down dropdown-icon"></i>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a href="#"><i class="fas fa-star"></i> Reviews</a></li>
                        <li><a href="#"><i class="fas fa-calendar-check"></i> Bookings</a></li>
                    </ul>
                </li>
                <li><a href="#"><i class="fas fa-envelope"></i> Contact</a></li>
                <li><a href="#"><i class="fas fa-user"></i> Profile</a></li>
            </ul>
        </nav>
    </header>

    <!-- Main Container -->
    <div class="container">
        <!-- Page Title -->
        <div class="page-title">
            <h1><i class="fas fa-calendar-alt"></i> Book Your Journey</h1>
            <p>Choose your perfect vehicle and travel dates</p>
        </div>

        <!-- Filter Section -->
        <div class="filter-section">
            <h2 class="filter-title">
                <i class="fas fa-filter"></i>
                Search & Filter Options
            </h2>
            
            <div class="filter-grid">
                <div class="filter-group">
                    <label for="vehicleType"><i class="fas fa-car"></i> Vehicle Type</label>
                    <select id="vehicleType" class="filter-select">
                        <option value="">All Vehicles</option>
                        <option value="kdh">KDH Van</option>
                        <option value="dolphin">Dolphin</option>
                        <option value="car">Car</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label for="capacity"><i class="fas fa-users"></i> Passenger Capacity</label>
                    <select id="capacity" class="filter-select">
                        <option value="">Any Capacity</option>
                        <option value="1-4">1-4 Passengers</option>
                        <option value="5-8">5-8 Passengers</option>
                        <option value="9-15">9-15 Passengers</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label><i class="fas fa-calendar"></i> Date Range</label>
                    <div class="date-range">
                        <input type="date" id="startDate" class="filter-input">
                        <input type="date" id="endDate" class="filter-input">
                    </div>
                </div>
            </div>

            <div class="filter-buttons">
                <button class="btn btn-primary" onclick="searchVehicles()">
                    <i class="fas fa-search"></i> Search Vehicles
                </button>
                <button class="btn btn-secondary" onclick="clearFilters()">
                    <i class="fas fa-undo"></i> Clear Filters
                </button>
            </div>
        </div>

        <!-- Loading -->
        <div class="loading" id="loading">
            <div class="spinner"></div>
            Searching available vehicles...
        </div>

        <!-- Vehicles Section -->
        <div class="vehicles-section">
            <h2 class="section-title">Available Vehicles</h2>
            
            <div class="vehicles-grid" id="vehiclesGrid">
                <!-- KDH Van -->
                <div class="vehicle-card" data-type="kdh" data-capacity="15" data-price="80">
                    <div class="vehicle-image">
                        <i class="fas fa-bus"></i>
                    </div>
                    <div class="vehicle-info">
                        <span class="vehicle-type">KDH VAN</span>
                        <h3 class="vehicle-name">Toyota KDH Van</h3>
                        <div class="vehicle-features">
                            <span class="feature"><i class="fas fa-users"></i> 15 Seats</span>
                            <span class="feature"><i class="fas fa-snowflake"></i> AC</span>
                            <span class="feature"><i class="fas fa-wifi"></i> WiFi</span>
                            <span class="feature"><i class="fas fa-music"></i> Music System</span>
                        </div>
                        <div class="vehicle-price">$80 / Day</div>
                        <div class="availability-status status-available">
                            <i class="fas fa-check-circle"></i> Available
                        </div>
                        <button class="book-btn" onclick="bookVehicle('kdh', 'Toyota KDH Van')">
                            <i class="fas fa-calendar-plus"></i> Book Now
                        </button>
                    </div>
                </div>

                <!-- Dolphin -->
                <div class="vehicle-card" data-type="dolphin" data-capacity="8" data-price="60">
                    <div class="vehicle-image">
                        <i class="fas fa-car"></i>
                    </div>
                    <div class="vehicle-info">
                        <span class="vehicle-type">DOLPHIN</span>
                        <h3 class="vehicle-name">Nissan Dolphin</h3>
                        <div class="vehicle-features">
                            <span class="feature"><i class="fas fa-users"></i> 8 Seats</span>
                            <span class="feature"><i class="fas fa-snowflake"></i> AC</span>
                            <span class="feature"><i class="fas fa-suitcase"></i> Luggage Space</span>
                            <span class="feature"><i class="fas fa-shield-alt"></i> Safety Features</span>
                        </div>
                        <div class="vehicle-price">$60 / Day</div>
                        <div class="availability-status status-available">
                            <i class="fas fa-check-circle"></i> Available
                        </div>
                        <button class="book-btn" onclick="bookVehicle('dolphin', 'Nissan Dolphin')">
                            <i class="fas fa-calendar-plus"></i> Book Now
                        </button>
                    </div>
                </div>

                <!-- Car -->
                <div class="vehicle-card" data-type="car" data-capacity="4" data-price="40">
                    <div class="vehicle-image">
                        <i class="fas fa-car-side"></i>
                    </div>
                    <div class="vehicle-info">
                        <span class="vehicle-type">CAR</span>
                        <h3 class="vehicle-name">Toyota Sedan</h3>
                        <div class="vehicle-features">
                            <span class="feature"><i class="fas fa-users"></i> 4 Seats</span>
                            <span class="feature"><i class="fas fa-snowflake"></i> AC</span>
                            <span class="feature"><i class="fas fa-gas-pump"></i> Fuel Efficient</span>
                            <span class="feature"><i class="fas fa-road"></i> City Travel</span>
                        </div>
                        <div class="vehicle-price">$40 / Day</div>
                        <div class="availability-status status-unavailable">
                            <i class="fas fa-times-circle"></i> Unavailable
                        </div>
                        <button class="book-btn" disabled>
                            <i class="fas fa-ban"></i> Not Available
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Intro Section -->
        <section class="intro-section">
            <div class="content">
                <h2>Seamless Travel Awaits: Book Your Adventure Today!</h2>
                <p>At Thisara Travels and Tours, we make it easy for you to embark on your journey through the enchanting landscapes of Sri Lanka. Our user-friendly online booking system allows you to reserve your travel arrangements with just a few clicks. Choose from a wide selection of vehicles tailored to your needs, and enjoy the flexibility of customizing your pick-up and drop-off locations. Start your unforgettable experience today—your Sri Lankan adventure is just a click away!</p>
                <a href="https://wa.me/+94702180024" class="whatsapp-btn" target="_blank">
                    <i class="fab fa-whatsapp"></i> Contact Us (+94702180024)
                </a>
            </div>
            <div class="decorative-icon">
                <i class="fas fa-map"></i>
            </div>
        </section>

        <!-- Call-to-Action Section -->
        <section class="cta-section">
            <div class="decorative-icon">
                <i class="fas fa-route"></i>
            </div>
            <div class="content">
                <h2>Have Any Pre Booking Question?</h2>
                <p>Experience luxurious comfort and convenience with Thisara Travels & Tours's top-tier car booking service. Our spacious vehicles, accommodating up to 6 passengers without a driver, ensure a smooth journey to and from your desired destinations. Travel in style while enjoying the scenic beauty of Srilanka, all in the company of Texi's reliable and professional team.</p>
                <a href="https://wa.me/+94702180024" class="whatsapp-btn" target="_blank">
                    <i class="fab fa-whatsapp"></i> Contact Us (+94702180024)
                </a>
            </div>
        </section>
    </div>

    <script>
        // Set minimum date to today
        const today = new Date().toISOString().split('T')[0];
        document.getElementById('startDate').min = today;
        document.getElementById('endDate').min = today;

        // Update end date minimum when start date changes
        document.getElementById('startDate').addEventListener('change', function() {
            document.getElementById('endDate').min = this.value;
        });

        // Search Vehicles Function
        function searchVehicles() {
            const loading = document.getElementById('loading');
            const vehiclesGrid = document.getElementById('vehiclesGrid');
            
            // Show loading
            loading.style.display = 'block';
            vehiclesGrid.style.opacity = '0.5';
            
            // Get filter values
            const vehicleType = document.getElementById('vehicleType').value;
            const capacity = document.getElementById('capacity').value;
            const startDate = document.getElementById('startDate').value;
            const endDate = document.getElementById('endDate').value;
            
            // Simulate API call delay
            setTimeout(() => {
                filterVehicles(vehicleType, capacity);
                checkAvailability(startDate, endDate);
                
                // Hide loading
                loading.style.display = 'none';
                vehiclesGrid.style.opacity = '1';
            }, 1500);
        }

        // Filter Vehicles Function
        function filterVehicles(type, capacity) {
            const cards = document.querySelectorAll('.vehicle-card');
            
            cards.forEach(card => {
                let show = true;
                
                // Filter by vehicle type
                if (type && card.dataset.type !== type) {
                    show = false;
                }
                
                // Filter by capacity
                if (capacity) {
                    const cardCapacity = parseInt(card.dataset.capacity);
                    const [min, max] = capacity.split('-').map(n => parseInt(n.replace('+', '')));
                    
                    if (capacity.includes('+')) {
                        if (cardCapacity < min) show = false;
                    } else {
                        if (cardCapacity < min || cardCapacity > max) show = false;
                    }
                }
                
                // Show/hide card with animation
                if (show) {
                    card.style.display = 'block';
                    card.style.animation = 'fadeInUp 0.5s ease';
                } else {
                    card.style.display = 'none';
                }
            });
        }

        // Check Availability Function (simulated)
        function checkAvailability(startDate, endDate) {
            const cards = document.querySelectorAll('.vehicle-card');
            
            // Simulate availability check
            cards.forEach((card, index) => {
                const statusElement = card.querySelector('.availability-status');
                const bookButton = card.querySelector('.book-btn');
                
                // Randomly set availability for demo (in real app, this would check database)
                const isAvailable = Math.random() > 0.3;
                
                if (isAvailable) {
                    statusElement.className = 'availability-status status-available';
                    statusElement.innerHTML = '<i class="fas fa-check-circle"></i> Available';
                    bookButton.disabled = false;
                    bookButton.innerHTML = '<i class="fas fa-calendar-plus"></i> Book Now';
                } else {
                    statusElement.className = 'availability-status status-unavailable';
                    statusElement.innerHTML = '<i class="fas fa-times-circle"></i> Unavailable';
                    bookButton.disabled = true;
                    bookButton.innerHTML = '<i class="fas fa-ban"></i> Not Available';
                }
            });
        }

        // Clear Filters Function
        function clearFilters() {
            document.getElementById('vehicleType').value = '';
            document.getElementById('capacity').value = '';
            document.getElementById('startDate').value = '';
            document.getElementById('endDate').value = '';
            
            // Show all vehicles
            const cards = document.querySelectorAll('.vehicle-card');
            cards.forEach(card => {
                card.style.display = 'block';
                card.style.animation = 'fadeInUp 0.5s ease';
            });
        }

        // Book Vehicle Function
        function bookVehicle(type, name) {
            const startDate = document.getElementById('startDate').value;
            const endDate = document.getElementById('endDate').value;
            
            if (!startDate || !endDate) {
                alert('Please select your travel dates first!');
                return;
            }
            
            // In real application, this would redirect to booking form or show modal
            alert(`Booking request for ${name}\nDates: ${startDate} to ${endDate}\n\nYou will be redirected to the booking form.`);
            
            // Here you would typically:
            // 1. Collect additional booking details
            // 2. Send booking request to server
            // 3. Redirect to confirmation page
        }

        // Add smooth scroll behavior
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    </script>
</body>
</html>