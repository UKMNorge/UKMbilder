<?php
	add_action('wp_ajax_my_action', 'upload' );

	function upload()
	{
		/**
		 * upload.php
		 *
		 * Copyright 2009, Moxiecode Systems AB
		 * Released under GPL License.
		 *
		 * License: http://www.plupload.com/license
		 * Contributing: http://www.plupload.com/contributing
		 */
		nocache_headers();
		
		$upload_dir = wp_upload_dir();
		$targetDir = $upload_dir['path'];
		
		@set_time_limit(25 * 60);
		
		// Get parameters
		$chunk = isset($_REQUEST["chunk"]) ? $_REQUEST["chunk"] : 0;
		$chunks = isset($_REQUEST["chunks"]) ? $_REQUEST["chunks"] : 0;
		$fileName = isset($_REQUEST["name"]) ? $_REQUEST["name"] : '';
		
		// Clean the fileName for security reasons
		$fileName = preg_replace('/[^\w\._]+/', '', $fileName);
		$fileName = str_replace(array('æ,ø,å','(',')',' '),array('a,o,a','','',''), $fileName);
		
		// Make sure the fileName is unique but only if chunking is disabled
		if ($chunks < 2 && file_exists($targetDir . DIRECTORY_SEPARATOR . $fileName)) {
			$ext = strrpos($fileName, '.');
			$fileName_a = substr($fileName, 0, $ext);
			$fileName_b = substr($fileName, $ext);
		
			$count = 1;
			while (file_exists($targetDir . DIRECTORY_SEPARATOR . $fileName_a . '_' . $count . $fileName_b))
				$count++;
		
			$fileName = $fileName_a . '_' . $count . $fileName_b;
			
			error_log( 'Korrigert filnavn: '.$fileName );
		}
		
		// Create target dir
		if (!file_exists($targetDir))
			@mkdir($targetDir);

		// Look for the content type header
		if (isset($_SERVER["HTTP_CONTENT_TYPE"]))
			$contentType = $_SERVER["HTTP_CONTENT_TYPE"];
		
		if (isset($_SERVER["CONTENT_TYPE"]))
			$contentType = $_SERVER["CONTENT_TYPE"];
		
		// Handle non multipart uploads older WebKit versions didn't support multipart in HTML5
		if (strpos($contentType, "multipart") !== false) {
			error_log( 'MP - Filnavn: '.$fileName );
			if (isset($_FILES['async-upload']['tmp_name']) && is_uploaded_file($_FILES['async-upload']['tmp_name'])) {
				// Open temp file
				$out = fopen($targetDir . DIRECTORY_SEPARATOR . $fileName, $chunk == 0 ? "wb" : "ab");
				if ($out) {
					// Read binary input stream and append it to temp file
					$in = fopen($_FILES['async-upload']['tmp_name'], "rb");
		
					if ($in) {
						while ($buff = fread($in, 4096))
							fwrite($out, $buff);    
					} else {
						error_log('101');
						die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
					}
					fclose($in);
					fclose($out);
					@unlink($_FILES['async-upload']['tmp_name']);
				} else {
					error_log('102');
					die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
				}
			} else {
				error_log('103');
				die('{"jsonrpc" : "2.0", "error" : {"code": 103, "message": "Failed to move uploaded file."}, "id" : "id"}');
			}
		} else {
			// Open temp file
			$out = fopen($targetDir . DIRECTORY_SEPARATOR . $fileName, $chunk == 0 ? "wb" : "ab");
			if ($out) {
				// Read binary input stream and append it to temp file
				$in = fopen("php://input", "rb");
		
				if ($in) {
					while ($buff = fread($in, 4096))
						fwrite($out, $buff);
				} else {
					die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
					error_log( '101' );
				}
		
				fclose($in);
				fclose($out);
			} else {
				die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
				error_log( '102' );
			}
		}
		error_log('MP success!');
		// Return JSON-RPC response
		die('{"jsonrpc" : "2.0", "result" : null, "id" : "id"}');


	}