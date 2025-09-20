<?php
    define("TOOLKIT_PATH", '/var/www/html/');
    require_once(TOOLKIT_PATH . '_toolkit_loader.php');

    // Get file from command line arguments
    if ($argc != 2) {
        echo "Usage: php parse_metadata.php <metadata_file.xml>\n";
        exit(1);
    }
    $file = $argv[1];
    if (!file_exists($file)) {
        echo "File not found: $file\n";
        exit(1);
    }

    $parser = new OneLogin\Saml2\IdPMetadataParser();
    try {
        $metadata = $parser->parseFileXML($file);
        
        // Format metadata to resemble settings.php idp array structure
        echo "<?php\n\n";
        echo "// Identity Provider Data that we want connected with our SP.\n";
        echo "return array (\n";
        
        // Entity ID
        if (isset($metadata['idp']['entityId'])) {
            echo "    // Identifier of the IdP entity  (must be a URI)\n";
            echo "    'entityId' => '" . $metadata['idp']['entityId'] . "',\n";
        }
        
        // SSO Service
        if (isset($metadata['idp']['singleSignOnService'])) {
            echo "    // SSO endpoint info of the IdP. (Authentication Request protocol)\n";
            echo "    'singleSignOnService' => array (\n";
            echo "        // URL Target of the IdP where the Authentication Request Message\n";
            echo "        // will be sent.\n";
            echo "        'url' => '" . $metadata['idp']['singleSignOnService']['url'] . "',\n";
            echo "        // SAML protocol binding to be used when returning the <Response>\n";
            echo "        // message. SAML Toolkit supports the HTTP-Redirect binding\n";
            echo "        // only for this endpoint.\n";
            echo "        'binding' => '" . $metadata['idp']['singleSignOnService']['binding'] . "',\n";
            echo "    ),\n";
        }
        
        // SLO Service
        if (isset($metadata['idp']['singleLogoutService'])) {
            echo "    // SLO endpoint info of the IdP.\n";
            echo "    'singleLogoutService' => array (\n";
            echo "        // URL Location of the IdP where SLO Request will be sent.\n";
            echo "        'url' => '" . $metadata['idp']['singleLogoutService']['url'] . "',\n";
            echo "        // URL location of the IdP where the SP will send the SLO Response (ResponseLocation)\n";
            echo "        // if not set, url for the SLO Request will be used\n";
            echo "        'responseUrl' => '',\n";
            echo "        // SAML protocol binding to be used when returning the <Response>\n";
            echo "        // message. SAML Toolkit supports the HTTP-Redirect binding\n";
            echo "        // only for this endpoint.\n";
            echo "        'binding' => '" . $metadata['idp']['singleLogoutService']['binding'] . "',\n";
            echo "    ),\n";
        }
        
        // X.509 Certificate(s)
        if (isset($metadata['idp']['x509certMulti'])) {
            // x509certMulti is already structured with signing/encryption arrays
            echo "    'x509certMulti' => array (\n";
            if (isset($metadata['idp']['x509certMulti']['signing'])) {
                echo "        'signing' => array (\n";
                foreach ($metadata['idp']['x509certMulti']['signing'] as $index => $cert) {
                    echo "            $index => '$cert',\n";
                }
                echo "        ),\n";
            }
            if (isset($metadata['idp']['x509certMulti']['encryption'])) {
                echo "        'encryption' => array (\n";
                foreach ($metadata['idp']['x509certMulti']['encryption'] as $index => $cert) {
                    echo "            $index => '$cert',\n";
                }
                echo "        ),\n";
            }
            echo "    ),\n";
        } elseif (isset($metadata['idp']['x509cert'])) {
            // x509cert needs to be converted to x509certMulti format
            echo "    'x509certMulti' => array (\n";
            if (is_array($metadata['idp']['x509cert'])) {
                // Multiple certificates
                echo "        'signing' => array (\n";
                foreach ($metadata['idp']['x509cert'] as $index => $cert) {
                    echo "            $index => '$cert',\n";
                }
                echo "        ),\n";
                echo "        'encryption' => array (\n";
                foreach ($metadata['idp']['x509cert'] as $index => $cert) {
                    echo "            $index => '$cert',\n";
                }
                echo "        ),\n";
            } else {
                // Single certificate
                echo "        'signing' => array (\n";
                echo "            0 => '" . $metadata['idp']['x509cert'] . "',\n";
                echo "        ),\n";
                echo "        'encryption' => array (\n";
                echo "            0 => '" . $metadata['idp']['x509cert'] . "',\n";
                echo "        ),\n";
            }
            echo "    ),\n";
        }
        
        echo ");\n";
        
    } catch (Exception $e) {
        echo 'Exception returned:' . $e->getMessage() . "\n";
        exit();
    }
?>
