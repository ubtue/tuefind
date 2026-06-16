When migrating to 11.0, do NOT execute VuFind SQL patches (module/VuFind/sql/migrations) because they are not compatible with our local modifications.
Instead, please execute all files from this directory in the correct sequence.
After 004, please execute 004a or 004b depending on whether you have a KrimDok or IxTheo installation, and then continue with 005 in both cases.
When done with the files in this directory, please also execute the files from module/(IxTheo|KrimDok)/sql/migrations/mysql/11.0 depending on your installation type.
