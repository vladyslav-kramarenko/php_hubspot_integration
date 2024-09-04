# HubSpot Deal and Engagement Automation

This repository contains a PHP script that automates the creation of HubSpot contacts, deals, and engagement notes with attachments based on form submissions. The script maps form IDs to specific images and file attachments, which are then associated with the created deals in HubSpot.

## Features

- **Contact Creation:** Automatically creates or retrieves existing contacts in HubSpot based on form submissions.
- **Deal Creation:** Generates a new deal in HubSpot associated with the contact.
- **Engagement Creation:** Creates an engagement (note) with an attached image and file specific to the form submission.

## How It Works

1. **Form Submission:**
   - The script receives form data via an AJAX POST request.
   - It logs the received data for debugging purposes.

2. **Contact Handling:**
   - Checks if a contact already exists in HubSpot.
   - If the contact exists, retrieves the contact ID; otherwise, it creates a new contact.

3. **Deal Handling:**
   - Creates a new deal associated with the contact.

4. **Engagement Creation:**
   - Maps the submitted form ID to a specific image URL and HubSpot file ID.
   - Creates an engagement (note) with the associated image and attaches the corresponding file.

## Setup

### Prerequisites

- **PHP 7.4+**: Ensure your server is running a compatible version of PHP.
- **HubSpot API Key**: You need a valid HubSpot API key with appropriate permissions.

### Installation

1. **Clone the Repository:**

   ```bash
   git clone https://github.com/vladyslav-kramarenko/php_hubspot_integration.git
   cd php_hubspot_integration
