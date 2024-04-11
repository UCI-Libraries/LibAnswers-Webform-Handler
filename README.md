# LibAnswers Ticket Import Script

This PHP script is designed to automate the submission of tickets to the LibAnswers platform using API calls. It facilitates the collection of project details through a form submission, assembles the data, and uses the LibAnswers API to create a ticket on behalf of the user.

## Features

- Securely retrieves an access token using client credentials from a configuration file.
- Dynamically builds a detailed submission form payload including project team members, research question, type of project, affiliation, and more.
- Submits the ticket to LibAnswers using a POST request.
- Handles errors gracefully and logs relevant responses for debugging purposes.

## Prerequisites

Before deploying the script, ensure you have the following:

- PHP 7.2 or above installed on your server.
- cURL enabled in PHP for making API requests.
- Access to LibAnswers API with valid client credentials (`client_id` and `client_secret`).

## Installation

1. **Clone or Download the Script**

    Start by cloning the script to your web server's directory or download the PHP file directly.

    ```bash
    git clone https://your-repository-url /path/to/web/directory
    ```

2. **Set Up Configuration File**

    Create a `.ini` file named `libAnswersConfig.ini` under `/var/www/config/` directory. This file should contain your LibAnswers client credentials:

    ```ini
    client_id=your_client_id_here
    client_secret=your_client_secret_here
    ```

3. **Ensure Proper Permissions**

    Make sure the web server has read access to the `libAnswersConfig.ini` file and write access to the log directory for error logging.

4. **Adjust CORS Policy**

    The script sets the `Access-Control-Allow-Origin` header. Make sure to adjust the value to match your domain or remove this line if not necessary.

## Usage

The script can be executed directly by accessing it via a web browser or by submitting a POST request to the script's URL. The POST request should include all the necessary data as form fields that match the project detail requirements.

## Security Notes

- Ensure your `libAnswersConfig.ini` file is located outside the public web directory or properly secured to prevent unauthorized access.

## Troubleshooting

- If you encounter errors during token acquisition or ticket submission, check the web server's error log for detailed messages.
- Ensure your LibAnswers credentials are correct and have the necessary permissions.
- Verify that your PHP installation has cURL support enabled.
