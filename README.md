# com.joineryhq.dupmon

## CiviCRM: Dedupe Monitor

![Screenshot](/images/screenshot.png)

Eases duplicate detection by performing periodic background scans for duplicate 
candidates across all contacts, for each appropriately configured Dedupe Rule; 
duplicate candidate are presented in reviewable batches, which you can then process
for merging (or marking as "not duplicates") as usual with CiviCRM's merging features.

## Who needs it

You might like this if you've experienced any of these problems with applying 
Dedupe Rules to scan for potential duplicate contacts in CiviCRM:

* Scanning takes forever, and can lock up or crash your site if running against
  all contacts or against a large group.
* Scanning is tedious, and you're tired of sitting there waiting for the scanning
  results to load.
* Nobody's scanning for duplicates because of these headaches, and meanwhile
  your number of duplicates is increasing, which just makes the scanning take longer.
  
## What it does

When you're not looking, it applies your configured Dedupe Rules against all of 
your contacts, packages up any identified duplicate contacts into little batches,
and alerts you if any are found. It's careful enough not to lock up your site
during these scans, and much more convenient than sitting yourself in front of a screen 
scanning with each Dedupe Rule against small sub-sets of your contacts.

## Requirements

* MariaDB

This extension relies on MariaDB's MAX_EXECUTION_TIME variable to prevent
dedupe scans from locking up the site or otherwise running amok. 

Sites running MySQL can use it if they have smaller numbers of contacts. Such a
site probably doesn' have the "dedupe scans are crashing my site" problem, but they
may still have the "dedupe scans are tedious and inconvenient" problem, which
this extension can relieve.

## Usage

This extension starts working with sensible defaults automatically upon installation,
at the next Scheduled Job run, scanning all contacts against most of the existing
Dedupe Rules, and grouping any identified duplicate candidates into small batches
for your review.

For a list of all batches of duplicate candidates, navigate to the Batches page at:  
Contacts > Find and Merge Duplicate Contacts > Dedupe Monitor > Batches  
(example.com/civicrm/admin/dupmon/batches?reset=1)

To process the duplicate candidates in any batch on the Batches page, click the 
Dedupe link for that batch to open CiviCRM's native "Find and Merge Duplicate Contacts"
screen showing the results of the given Dedupe Rule for that batch of duplicate
candidates.

## Defaults

* Upon installation, all non-supervised Dedupe Rules are selected for monitoring.
  See Configuration, below, for more on how to enable or disable monitoring for
  any Dedupe Rule.
* A scheduled job is created (with an Hourly frequency by default) to scan a small
  chunk of contacts against each monitored Dedupe Rule.

## Configuration

To enable or disable the monitoring of any or all Dedupe Rules, navigate to the
Setting page at:  
Contacts > Find and Merge Duplicate Contacts > Dedupe Monitor > Batches  
(example.com/civicrm/admin/dupmon/settings?reset=1)

The Settings page also includes a few Advanced Settings that you may want to 
tweak for performance.

## License

The extension is licensed under [GPL-3.0](LICENSE.txt).
