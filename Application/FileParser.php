<?php

    namespace Application;

    class FileParser
    {
        protected array $appCodes;
        protected array $directoryListing;
        protected string $basePath;
        protected string $dataBasePath;
        protected mixed $csvHandle;

        public function __construct()
        {
            $this->basePath = str_replace('Application' . DIRECTORY_SEPARATOR . 'FileParser.php', '', realpath(__FILE__));
            $this->dataBasePath = $this->basePath . 'data' . DIRECTORY_SEPARATOR;
        }

        /**
         * getDirectoryList - given a directory return a list of .log files contained
         * within the target directories. Ultimately we're going to loop through this
         * as a flat array, so we don't really need to nest it or anything like that.
         *
         * @param string $basePath The path to start searching from
         * @return array
         */
        protected function getDirectoryListing(string $basePath = '') :array {

            if ($basePath) {
                $this->dataBasePath = $basePath;
            }

            echo 'Getting files in ' . $this->dataBasePath . "\n";

            $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($basePath));

            foreach ($iterator as $file) {

                if (!$file->isDir()) {

                    if (pathinfo($file, PATHINFO_EXTENSION) == 'log') {

                        $this->directoryListing[] = $file->getPathname();
                    }
                }
            }
            return $this->directoryListing;

        }

        /**
         * setappCodes - grab the ini file and load it as an array
         *
         * @return array
         */
        protected function setappCodes() :array
        {
            $this->appCodes = parse_ini_file($this->basePath . 'appCodes.ini');

            return $this->appCodes;
        }

        /**
         * openCSV -open our csv file ready for writing. Note that
         * we're hinting a 'mixed' type here. Not ideal, but PHP
         * doesn't support 'stream' as a type hint yet.
         *
         * @param string $fileName
         * @return mixed
         */
        protected function openCSV(string $fileName = '') :mixed
        {
            if (!$fileName) {
                $fileName = 'device-tokens' . time() . '.csv';
            }

            $filePath = $this->basePath . $fileName;

            $this->csvHandle = fopen($filePath, 'w');

            return $this->csvHandle;
        }

        /**
         * writeHeaders - write the headers to the csv file
         *
         * @return void
         */
        protected function writeHeaders() :void
        {
            echo "Writing headers\n";

            if (!$this->csvHandle)
            {
                $this->openCSV();
            }

            fputcsv($this->csvHandle, ['id', 'appcode', 'deviceId', 'contactable', 'subscription_status', 'has_downloaded_free_product_staus', 'has_downloaded_iap_product_status']);
        }

        /**
         * parseFiles - loop through the files, split the lines, process the
         * result and add it to the csv
         *
         * @return void
         */
        protected function parseFiles()
        {
            echo "Parsing log files and writing csv\n";

            $id = 0;

            foreach($this->directoryListing as $logFile)
            {
                $fileHandle = file($logFile);
                
                $skipFirstLine = true;

                foreach($fileHandle as $line) {

                        // First line is headers, so we just skip it.
                        if ($skipFirstLine) {
                        $skipFirstLine = false;
                        continue;
                    }
                
                    // Ultimately we're passing a flat array here,
                    // but I'm setting up an assoc array to make it
                    // easier to ensure we fill in the correct fields
                    $csvArray = [
                        'id'                                => $id, 
                        'appCode'                           => '', 
                        'deviceId'                          => '', 
                        'contactable'                       => '', 
                        'subscription_status'               => '', 
                        'has_downloaded_free_product_staus' => '', 
                        'has_downloaded_iap_product_status' => ''
                    ];
                    


                    // Get rid of our line endings (str_replace is faster that preg_replace)
                    $line = str_replace(array("\r", "\n"), '', $line);
                    $parts = explode(',', $line);

                    $tags = explode('|', $parts[3]);

                    $csvArray['appCode']                            = array_search($parts[0], $this->appCodes);
                    $csvArray['deviceId']                           = $parts[1];
                    $csvArray['contactable']                        = $parts[2] == 1 ? 1 : 0;
                    
                    if (is_array($tags)) {
                        $csvArray['subscription_status']                = $this->subscriptionStatus($tags);
                        $csvArray['has_downloaded_free_product_status']  = $this->freeProductStatus($tags);
                        $csvArray['has_downloaded_iap_product_status']  = $this->iapDownloadStatus($tags);
                    }

                    fputcsv($this->csvHandle, $csvArray);
                    
                    $id++;
                }
            }

            fclose($this->csvHandle);
        }

        /**
         * subscriptionStatus - return a string containing any subscription status
         *
         * @param string $tags
         * @return boolean
         */
        protected function subscriptionStatus(array $tags) :string {

            $testArray = [
                'active_subscriber', 
                'expired_subscriber',
                'never_subscribed',
                'subscription_unknown',
            ];

            return implode('|', array_intersect($tags, $testArray));
        }

        /**
         * freeProductStatus - return a string containing has_downloaded_free_product_status
         * tags in a pipe delimited string
         * 
         * @param array $tags
         * @return string
         */
        protected function freeProductStatus(array $tags) :string {

            $testArray = [
                'has_downloaded_free_product', 
                'not_downloaded_free_product',
                'downloaded_free_product_unknown ',
            ];
            
            return implode('|', array_intersect($tags, $testArray));
        }

        
        /**
         * freeProductStatus - return a string containing has_downloaded_iap_product_status
         * tags in a string
         *
         * @param array $tags
         * @return string
         */
        protected function iapDownloadStatus(array $tags) :string {

            $testArray = [
                'has_downloaded_iap_product', 
                'not_downloaded_iap_product',   // This is listed as 'not_downloaded_free_product' in the test instructions. I assume this is in error.
                'downloaded_iap_product_unknown',
            ];
            
            return implode('|', array_intersect($tags, $testArray));
        }

        /**
         * writeCSV - sticks all the above together so it can be run
         * from a php file with a single method call and default settings
         *
         * @return void
         */
        public function writeCSV(string $basePath = './data')
        {
            $this->getDirectoryListing($basePath);
            $this->setappCodes();
            $this->openCSV();
            $this->writeHeaders();
            $this->parseFiles();
        }
    }    