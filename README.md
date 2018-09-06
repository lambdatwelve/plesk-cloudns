# plesk-cloudns-event
Automagically adds/removes slave DNS zones in ClouDNS when events occur in Plesk

# What it does
This project allows simple **slave** zone creation/deletion in [ClouDNS](https://www.cloudns.net) for all created/deleted domains in a [Plesk Onyx](https://www.plesk.com/) installation. Every time a domain is created (or deleted) in Plesk, the relevant slave zone is created (or deleted) in ClouDNS.

After successful setup, Plesk DNS can be blocked in the firewall for the wider internet, preventing numerous attack vectors on your servers, and zone creation is event based instead of cron.

# What it does not
This project will not configure ClouDNS or Plesk to work together (see docs below though). 
It does not have a graphic interface - you need to edit the `ClouDNS.php` file provided and upload it to a specific path as root.

# Assumptions
To be able to use this project, several assumptions are made regarding your setup:

- You have Plesk Onyx (untested in previous versions, should work).
- You have SSH access to your server as root (or can sudo).
- You have PHP with the `curl` module enabled in your server CLI (use `php -m | grep curl` to check in an SSH session, should be default)
- You want to use ClouDNS as a slave, and Plesk as a master for all domains created after installation
  - This means that DNS records are managed in Plesk, and are mirrored in ClouDNS

# Installation

  * Login to ClouDNS, and create an API user. You should have an ***auth-id*** and ***password***.
  * Place the `ClouDNS.php` file in the `/usr/local/psa/admin/plib/registry/EventListener/` directory
  * Edit the file and add ***auth_id*** and ***password*** in the relevant sections
  * (Optional) Define which IP should be the master IP that ClouDNS talks to. If left blank, the script will try to determine it automagically.
  * Enjoy!
  
# Basic troubleshooting
- In ClouDNS, your API user has **ALL** (read: both IPv4 and IPv6) IPs allowed, not just your primary.
- In Plesk, you allow ClouDNS to transfer zones as slave under Tools & Settings > General Settings > DNS Template > Transfer Restrictions Template. **ALL** your listed nameservers should be there (both IPv4 and IPv6), in order to properly propagate records.
- Having configured ClouDNS does not mean that the records are actually used if the 
