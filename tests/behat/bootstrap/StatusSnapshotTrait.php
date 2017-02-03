<?php

namespace DigidepsBehat;

use Behat\Behat\Hook\Scope\AfterScenarioScope;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;

trait StatusSnapshotTrait
{
    /**
     * @Then I save the application status into :status
     */
    public static function iSaveTheApplicationStatusInto($status)
    {
        $sqlFile = self::getSnapshotPath($status);
        /*
         * Dump data only, and prepend a truncate of all the tables
         * Take 30-40% of the time taken to dump structure, that never changes in the same suite
         *
         * */
        exec('pg_dump ' . self::$dbName . " -a --inserts | sed '/EXTENSION/d' > {$sqlFile}");
        file_put_contents($sqlFile,
            "SET client_min_messages TO WARNING; \n\n"
            ."truncate table casrec,audit_log_entry,safeguarding,role, migrations, transaction_type, deputy_case, report, audit_log_entry, odr,dd_user  RESTART IDENTITY cascade; \n\n"
            .file_get_contents($sqlFile));
    }

    /**
     * @Then I load the application status from :status
     */
    public static function iLoadtheApplicationStatusFrom($status)
    {
        $sqlFile = self::getSnapshotPath($status);
        if (!file_exists($sqlFile)) {
            $error = "File $sqlFile not found. Re-run the full behat suite to recreate the missing snapshots.";
            throw new \RuntimeException($error);
        }
        exec('psql ' . self::$dbName . " --quiet < {$sqlFile}");
    }

    /**
     * @param string $name
     *
     * @return string
     */
    private static function getSnapshotPath($name)
    {
        return '/tmp/behat/behat-snapshot-'
                . strtolower(preg_replace('/[^\w]+/', '-', $name))
                . '.sql';
    }

    /**
     * @Given I reset the behat SQL snapshots
     */
    public function deleteBehatSnapshots()
    {
        foreach (glob('/tmp/behat/behat-snapshot-*.sql') as $file) { // iterate files
          if (is_file($file)) {
              unlink($file);
          }
        }
    }

    /**
     * @BeforeScenario
     */
    public function dbSnapshotBeforeScenario(BeforeScenarioScope $scope)
    {
        if (!self::$autoDbSnapshot) {
            return;
        }

        $snapshotName = preg_replace('/([^a-z0-9])/i', '-', $scope->getScenario()->getTitle())
                        . '-before-auto';

        self::iSaveTheApplicationStatusInto($snapshotName);
    }

    /**
     * @AfterScenario
     */
    public function dbSnapshotAFterScenario(AfterScenarioScope $scope)
    {
        if (!self::$autoDbSnapshot) {
            return;
        }

        $snapshotName = preg_replace('/([^a-z0-9])/i', '-', $scope->getScenario()->getTitle())
                        . '-after-auto';

        self::iSaveTheApplicationStatusInto($snapshotName);
    }
}
