# com.joineryhq.dupmon

Sample code:
```php
<?php
$timeLimitSeconds = 5;
$myContactCount = 10000;
echo "\n======== Start a.php (contactCount = $myContactCount; timeLimitSeconds = $timeLimitSeconds)\n";
$dao = CRM_Core_DAO::executeQuery("select id, title, contact_type from civicrm_dedupe_rule_group where id /* in (12, 13, 14, 21, 22, 7) */");
while ($dao->fetch()) {
  foreach (array(TRUE, FALSE) as $checkPerms) {
    $cids = [];
    $cids = myGetCids($dao->contact_type, $myContactCount);
    echo "Rule {$dao->id} ({$dao->title}) for {$dao->contact_type}, checkPerms = {$checkPerms}, contactCount = " . count($cids) . ": ";
    if (
      0 && // Skip this for now
      $myContactCount > 1000
      && (
        ($dao->id == 7) 
        || ($dao->id == 12) 
        || ($dao->id == 13) 
        || ($dao->id == 14) 
        || ($dao->id == 17) 
        || ($dao->id == 21) 
        || ($dao->id == 22)
      )
    ){
      echo "SKIPPING (known bad performance on count of $myContactCount)\n";
      continue;
    }
    $startTime = microtime(TRUE);
    $variableDao = CRM_Core_DAO::executeQuery("SHOW VARIABLES LIKE '%MAX_STATEMENT_TIME%'");
    $variableDao->fetch();
    $originalTimeLimit = $variableDao->Value;
    CRM_Core_DAO::executeQuery('SET SESSION MAX_STATEMENT_TIME=' . $timeLimitSeconds);
    $isTimeoutError = FALSE;
    try {
      $dupes = CRM_Dedupe_Finder::dupes($dao->id, $cids, $checkPerms);
    }
    catch (PEAR_Exception $e) {
      if ($e->getMessage() == "DB Error: unknown error") {
        $isTimeoutError = TRUE;
      }
      else {
        throw $e;
      }
    }
    CRM_Core_DAO::executeQuery('SET SESSION MAX_STATEMENT_TIME=' . $originalTimeLimit);
    $endTime = microtime(TRUE);
    if ($isTimeoutError) {
      echo "Timeout Error encountered\n";
    }
    else {
      echo "Found Pairs: ". count($dupes) . "\n";
      echo "    completed in seconds: " . ($endTime- $startTime) . "\n";
    }
  }
}

function myGetCids($contactType, $limit = 0) {
  $query ="select id from civicrm_contact where contact_type = '{$contactType}' and not is_deleted";
  if ($limit) {
    $query .= " limit {$limit}";
  }
  $dao = CRM_Core_DAO::executeQuery($query);
  $cids = [];

  while ($dao->fetch())  {
    $cids[] = $dao->id;
  }
  return $cids;
}
exit;

```


![Screenshot](/images/screenshot.png)

(*FIXME: In one or two paragraphs, describe what the extension does and why one would download it. *)

The extension is licensed under [GPL-3.0](LICENSE.txt).

## Requirements

* PHP v7.4+
* CiviCRM (*FIXME: Version number*)

## Installation (Web UI)

Learn more about installing CiviCRM extensions in the [CiviCRM Sysadmin Guide](https://docs.civicrm.org/sysadmin/en/latest/customize/extensions/).

## Installation (CLI, Zip)

Sysadmins and developers may download the `.zip` file for this extension and
install it with the command-line tool [cv](https://github.com/civicrm/cv).

```bash
cd <extension-dir>
cv dl com.joineryhq.dupmon@https://github.com/FIXME/com.joineryhq.dupmon/archive/master.zip
```
or
```bash
cd <extension-dir>
cv dl com.joineryhq.dupmon@https://lab.civicrm.org/extensions/com.joineryhq.dupmon/-/archive/main/com.joineryhq.dupmon-main.zip
```

## Installation (CLI, Git)

Sysadmins and developers may clone the [Git](https://en.wikipedia.org/wiki/Git) repo for this extension and
install it with the command-line tool [cv](https://github.com/civicrm/cv).

```bash
git clone https://github.com/FIXME/com.joineryhq.dupmon.git
cv en dupmon
```
or
```bash
git clone https://lab.civicrm.org/extensions/com.joineryhq.dupmon.git
cv en dupmon
```

## Getting Started

(* FIXME: Where would a new user navigate to get started? What changes would they see? *)

## Known Issues

(* FIXME *)
