# ThisaraTravels

ThisaraTravels is a web-based vehicle rental platform developed by five final-year UOB-SE students (SLIIT affiliate) as an Agile group project for a real client.

## Project Overview

- **Methodology:** Agile SCRUM, PRINCE2  
- **Duration:** 10 weeks  
- **Team:** 5 students, SLIIT affiliate

## Features

- **Home Page:** Public landing page (no login required)
- **About Us:** Agency information, services, and marketing content (no login required)
- **Reviews Page:**  
    - View reviews (no login required)  
    - Post reviews (login required)
- **Login/Sign In:** Gmail authentication only; user data stored in MongoDB
- **Rent Vehicle:**  
    - Search by date, destination, and number of people  
    - View available vehicles and request bookings
- **Admin Dashboard:**  
    - View, accept, or delete booking requests
- **Profile:**  
    - Manage contact info  
    - Post reviews  
    - View booking status (pending, rejected, accepted)

## Booking & Payment Flow

- Users request vehicle bookings; requests appear as pending on the admin dashboard.
- Admin can accept or delete requests. Accepted bookings make the vehicle unavailable for those dates.
- Upon completion or deletion, the vehicle becomes available again.
- **Payment is handled offline after vehicle return and is not tied to the website.**

## Tech Stack

- **Frontend:** HTML
- **Backend:** PHP
- **Database:** MongoDB Cloud Clusters (accounts, vehicles, reviews, bookings)
- **Hosting:** Custom domain

---

> This project is part of the UOB-SE final year curriculum at SLIIT, following Agile and PRINCE2 methodologies for real-world client engagement.
