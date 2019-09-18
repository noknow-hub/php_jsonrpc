<?php
//////////////////////////////////////////////////////////////////////
// jsonrpc.php
//
// @usage
//
//     1. Load this file.
//
//         --------------------------------------------------
//         require_once('jsonrpc.php');
//         use noknow\lib\api\jsonrpc;
//         --------------------------------------------------
//
//     2. Initialize JsonRpc class.
//
//         --------------------------------------------------
//         $jsonRpc = new jsonrpc\JsonRpc();
//         --------------------------------------------------
//
//     3. Now, you can use it!!
//
//         3-A. Here is a sample JSON.
//
//             --------------------------------------------------
//             {
//                 'id': 1,
//                 'jsonrpc': '2.0',
//                 'method': 'account',
//                 'params': {'name': 'noknow', 'age': 18},
//             }
//             --------------------------------------------------
//
//         3-1. When receiving HTTP request for JSON RPC, it would be good to get a JSON of asscociative array.
//
//             --------------------------------------------------
//             $json = $jsonRpc->GetJson();
//             if(is_null($json)) {
//                 // Error handling.
//             }
//             $id = $json['id'];  // 1
//             $method = $json['method'];  // 'hello-noknow'
//             $jsonrpc = $json['jsonrpc'];  // '2.0'
//             $params = $json['params'];  // {'name': 'noknow', 'age': 18}
//             --------------------------------------------------
//
//         3-2. When getting a part of the string / int type value of the params in JSON data.
//
//             --------------------------------------------------
//             $name = $jsonRpc->GetParamsString($params, 'name');
//             if(is_null($name)) {
//                 // Error handling.
//             }
//             $age = $jsonRpc->GetParamsInt($params, 'age');
//             if(is_null($age)) {
//                 // Error handling.
//             }
//             --------------------------------------------------
//
//         3-3. When responding.
//
//             --------------------------------------------------
//             // Response an empty.
//             $jsonRpc->ResEmpty();
//
//             // Response an error.
//             // {
//             //     'id': 1,
//             //     'jsonrpc': '2.0',
//             //     'error': 'This is an error message.',
//             // }
//             $jsonRpc->ResError($id, 'This is an error message.');
//             
//             // Response a success.
//             // {
//             //     'id': 1,
//             //     'jsonrpc': '2.0',
//             //     'success': 'This is a success message.',
//             // }
//             $jsonRpc->ResSuccess($id, 'This is a success message.');
//             --------------------------------------------------
//
//
// MIT License
//
// Copyright (c) 2019 noknow.info
//
// Permission is hereby granted, free of charge, to any person obtaining a copy
// of this software and associated documentation files (the "Software"), to deal
// in the Software without restriction, including without limitation the rights
// to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
// copies of the Software, and to permit persons to whom the Software is
// furnished to do so, subject to the following conditions:
//
// The above copyright notice and this permission notice shall be included in all
// copies or substantial portions of the Software.
//
// THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED,
// INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A 
// PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
// HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
// OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE
// OR THE USE OR OTHER DEALINGS IN THE SOFTW//ARE.
//////////////////////////////////////////////////////////////////////

namespace noknow\lib\api\jsonrpc;

class JsonRpc {

    //////////////////////////////////////////////////////////////////////
    // Properties
    //////////////////////////////////////////////////////////////////////
    private $jsonRpcVersion = '2.0';
    private $version;


    //////////////////////////////////////////////////////////////////////
    // Constructor
    //////////////////////////////////////////////////////////////////////
    public function __construct() {
        $this->version = phpversion();
    }


    //////////////////////////////////////////////////////////////////////
    // Get json.
    //////////////////////////////////////////////////////////////////////
    public function GetJson(): ?array {
        $jsonStr = file_get_contents('php://input');
        if($jsonStr === FALSE) {
            return NULL;
        }
        $jsonObj = json_decode($jsonStr, true);
        if(is_null($jsonObj)) {
            return NULL;
        }
        if(!array_key_exists('id', $jsonObj) ||
                !array_key_exists('method', $jsonObj) ||
                !array_key_exists('jsonrpc', $jsonObj) ||
                !array_key_exists('params', $jsonObj)) {
            return NULL;
        }

        $id = $jsonObj['id'];
        $method = $jsonObj['method'];
        $jsonrpc = $jsonObj['jsonrpc'];
        $params = $jsonObj['params'];

        if(gettype($id) !== 'integer' ||
                gettype($method) !== 'string' ||
                gettype($jsonrpc) !== 'string') {
            return NULL;
        }
        if($jsonrpc !== $this->jsonRpcVersion) {
            return NULL;
        }
        
        return array(
            'id' => $id,
            'method' => $method,
            'jsonrpc' => $jsonrpc,
            'params' => $params,
        );
    }


    //////////////////////////////////////////////////////////////////////
    // Get the string value by the key in params. 
    //////////////////////////////////////////////////////////////////////
    public function GetParamsString($params, string $key): ?string {
        if(gettype($params) === 'string') {
            return $params;
        } else if(gettype($params) === 'object') {
            if(is_null($key)) {
                return json_encode($params);
            } else {
                if(property_exists($params, $key)) {
                    $v = $params->{$key};
                } else {
                    return NULL;
                }
            }
        } else if(gettype($params) === 'array') {
            if(is_null($key)) {
                return implode(',', $params);
            } else {
                if(array_key_exists($key, $params)) {
                    $v = $params[$key];
                } else {
                    return NULL;
                }
            }
        } else {
            return NULL;
        }
        if(gettype($v) === 'string') {
            return $v;
        } else {
            return NULL;
        }
    }


    //////////////////////////////////////////////////////////////////////
    // Get the int value by the key in params. 
    //////////////////////////////////////////////////////////////////////
    public function GetParamsInt($params, string $key = NULL): ?int {
        if(gettype($params) === 'integer') {
            return $params;
        } else if(gettype($params) === 'object') {
            if(is_null($key)) {
                return NULL;
            } else {
                if(property_exists($params, $key)) {
                    $v = $params->{$key};
                } else {
                    return NULL;
                }
            }
        } else if(gettype($params) === 'array') {
            if(is_null($key)) {
                return NULL;
            } else {
                if(array_key_exists($key, $params)) {
                    $v = $params[$key];
                } else {
                    return NULL;
                }
            }
        } else {
            return NULL;
        }
        if(gettype($v) === 'integer') {
            return $v;
        } else {
            return NULL;
        }
    }


    //////////////////////////////////////////////////////////////////////
    // Response empty data.
    //////////////////////////////////////////////////////////////////////
    public function ResEmpty(): void {
        header("Content-Type: application/json;");
        echo '';
    }


    //////////////////////////////////////////////////////////////////////
    // Response error.
    //////////////////////////////////////////////////////////////////////
    public function ResError(int $id, $params): void {
        header("Content-Type: application/json;");
        $json = array(
            'id' => $id,
            'jsonrpc' => $this->jsonRpcVersion,
            'error' => $params,
        );
        $jsonStr = json_encode($json);
        if($jsonStr === FALSE) {
            $this->ResEmpty();
        } else {
            echo $jsonStr;
        }
    }


    //////////////////////////////////////////////////////////////////////
    // Response success.
    //////////////////////////////////////////////////////////////////////
    public function ResSuccess(int $id, $params): void {
        header("Content-Type: application/json;");
        $json = array(
            'id' => $id,
            'jsonrpc' => $this->jsonRpcVersion,
            'result' => $params,
        );
        $jsonStr = json_encode($json);
        if($jsonStr === FALSE) {
            $this->ResEmpty();
        } else {
            echo $jsonStr;
        }
    }


    //////////////////////////////////////////////////////////////////////
    // Set JSON RPC version.
    //////////////////////////////////////////////////////////////////////
    public function SetJsonRpcVersion(string $version): void {
        $this->jsonRpcVersion = $version;
    }


    //////////////////////////////////////////////////////////////////////
    // Get JSON RPC version.
    //////////////////////////////////////////////////////////////////////
    public function GetJsonRpcVersion(): string {
        return $this->jsonRpcVersion;
    }

}
