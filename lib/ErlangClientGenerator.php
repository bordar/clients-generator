<?php
class ErlangClientGenerator extends ClientGeneratorFromXml
{
	protected $reservedActionNames = array('list', 'end');
	
	function __construct($xmlPath, Zend_Config $config, $sourcePath = "erlang")
	{
		parent::__construct($xmlPath, $sourcePath, $config);
	}
	
	function getSingleLineCommentMarker()
	{
		return '%';
	}
	
	function generate()
	{
		parent::generate();
	
		$xpath = new DOMXPath($this->_doc);
		
    	$this->writeTypes();
    	
    	$this->startNewTextBlock();
		$this->appendLine('-include_lib("borhan_client_types.hrl").');
		$this->appendLine();
		
		$this->appendLine('-type void() :: void.');
		$this->appendLine();
		
		$this->appendLine('-record(borhan_error, {');
		$this->appendLine('	code = null :: string(),');
		$this->appendLine('	message = null :: string(),');
		$this->appendLine('	args = [] :: list()');
		$this->appendLine('}).');
		$this->appendLine();
		
		$this->appendLine('-record(borhan_configuration, {');
		$this->appendLine('	url = "http://www.borhan.com/api_v3" :: string(),');
		$this->appendLine('	client_options = [{verbose, false}] :: httpc:options(),');
		$this->appendLine('	request_options = [{timeout, 90000}] :: httpc:http_options()');
		$this->appendLine('}).');
		$this->appendLine();
		
		$this->appendLine('-record(borhan_request, {');
		$configurationNodes = $xpath->query("/xml/configurations/*");
	    $this->writeRequestProperties($configurationNodes);
		$this->appendLine('}).');
		$this->appendLine();
				
		$this->appendLine('borhan_request_to_proplist(#borhan_request{} = Rec) ->');
		$this->appendLine('	lists:zip(record_info(fields, borhan_request), tl(tuple_to_list(Rec))).');
		$this->appendLine();
		
    	$this->addFile("src/borhan_client.hrl", $this->getTextBlock());
		
		$serviceNodes = $xpath->query("/xml/services/service");
		foreach($serviceNodes as $serviceNode)
		{
			$this->writeService($serviceNode);
		}
	}
	
	function writeTypes()
	{
		$xpath = new DOMXPath($this->_doc);
		
    	$this->startNewTextBlock();
		$this->appendLine();
	    
		$enumNodes = $xpath->query("/xml/enums/enum");
		foreach($enumNodes as $enumNode)
		{
			$this->writeEnum($enumNode);
		}
		$this->appendLine();
    	
		$this->appendLine("-record(borhan_object_base, {");
		$this->appendLine("}).");
		$this->appendLine();
		
		$classNodes = $xpath->query("/xml/classes/class");
		foreach($classNodes as $classNode)
		{
			$this->writeRecord($classNode);
		}
		$isFirst = true;
		foreach($classNodes as $classNode)
		{
			$this->writeCastToProplist($classNode, $isFirst);
			$isFirst = false;
		}
		$this->appendLine('.');
    	$this->addFile("src/borhan_client_types.hrl", $this->getTextBlock());
	}
	
	function writeEnum(DOMElement $enumNode)
	{
		if(!$enumNode->childNodes->length)
			return;
			
		$enumName = $enumNode->getAttribute("name");
		if(!$this->shouldIncludeType($enumName))
			return;
		
		$constants = array();		
		if($this->generateDocs)
	 		$this->appendLine("% Enum $enumName");
	 		
		foreach($enumNode->childNodes as $constNode)
		{
			if ($constNode->nodeType != XML_ELEMENT_NODE)
				continue;
				
			$propertyName = $constNode->getAttribute("name");
			$propertyValue = $constNode->getAttribute("value");
			if ($enumNode->getAttribute("enumType") == "string")
				$propertyValue = "'$propertyValue'";
				
			$constants[] = $propertyValue;		
			if($this->generateDocs)
		 		$this->appendLine("% const $propertyName $propertyValue");
		}
		
		$erlangName = $this->camelCaseToUnderscoreAndLower($enumName);
	 	$this->appendLine("-type {$erlangName}() :: " . implode(' | ', $constants) . ".");
		$this->appendLine();
	}
	
	function getErlangPropetryName($name)
	{
		if($name == 'not')
			return 'not_';
			
		if(preg_match('/^[A-Z]/', $name));
			return lcfirst($name);
			
		return $name;
	}
	
	function getErlangTypeOrRecord($type, $arrayType = null, $enumType = null)
	{
		if(!$type)
			return null;
			
		switch($type)
		{
			case 'array':
				return '[' . $this->getErlangTypeOrRecord($arrayType) . ']';
				
			case 'int':
			case 'bigint':
				return 'integer()';
				
			case 'bool':
				return 'boolean()';
				
			default:
				$returnType = $this->camelCaseToUnderscoreAndLower($type);
				if(!$enumType && preg_match('/^Borhan/', $type))
					return "#{$returnType}{}";
				
				return "{$returnType}()";
		}
	}
	
	function writeRecordProperties(DOMElement $classNode)
	{
		$type = $classNode->getAttribute("name");
		if(!$this->shouldIncludeType($type))
			return;
		
		$isFirst = true;
		if ($classNode->hasAttribute("base"))
		{
			$xpath = new DOMXPath($this->_doc);
			$parentClassName = strval($classNode->getAttribute("base"));
			$parentClassNodes = $xpath->query("/xml/classes/class[@name = '$parentClassName']");
			$parentClassNode = $parentClassNodes->item(0);
			$isFirst = $this->writeRecordProperties($parentClassNode);
		}
			
		$title = "$type properties:";
		
		foreach($classNode->childNodes as $propertyNode)
		{
			if ($propertyNode->nodeType != XML_ELEMENT_NODE)
				continue;
			
			if(!$isFirst)
			{
				$this->appendLine(',');
			}
			
			if($title)
			{
				$this->appendLine();
				$this->appendLine("	% $title");
				$title = null;
			}
				
			$this->appendLine();
			
			$propName = $this->getErlangPropetryName($propertyNode->getAttribute("name"));
			$isReadyOnly = $propertyNode->getAttribute("readOnly") == 1;
			$isInsertOnly = $propertyNode->getAttribute("insertOnly") == 1;
			$propDescription = $propertyNode->getAttribute("description");
			$propDescription = trim($propDescription, " \t\n\r");
			
			$description = str_replace("\n", "\n	% ", $propDescription); // to format multiline descriptions
			if(strlen($propDescription))
				$this->appendLine("	% " . $description);
			
			if ($isReadyOnly )
				$this->appendLine("	% @readonly");
			if ($isInsertOnly)
				$this->appendLine("	% @insertonly");
			
			$propType = $this->getErlangTypeOrRecord($propertyNode->getAttribute("type"), $propertyNode->getAttribute("arrayType"), $propertyNode->getAttribute("enumType"));
			
			$this->append("	$propName = null :: $propType");
			$isFirst = false;
		}
		
		return $isFirst;
	}
	
	function writeRecord(DOMElement $classNode)
	{
		$type = $classNode->getAttribute("name");
		if(!$this->shouldIncludeType($type))
			return;
		
		$erlangName = $this->camelCaseToUnderscoreAndLower($type);
		
		if($this->generateDocs)
		{
			$this->appendLine("% Type $type");
		}
		$this->appendLine("-record($erlangName, {");
		$this->writeRecordProperties($classNode);
		$this->appendLine();
		$this->appendLine("}).");
		$this->appendLine();
	}
	
	function writeCastToProplist(DOMElement $classNode, $isFirst)
	{
		$type = $classNode->getAttribute("name");
		if(!$this->shouldIncludeType($type))
			return;
		
		if(!$isFirst)
			$this->appendLine(';');
	
		$erlangName = $this->camelCaseToUnderscoreAndLower($type);
		
		$this->appendLine();
		if($this->generateDocs)
		{
			$this->appendLine("% Type $type");
		}
		$this->appendLine("borhan_object_to_proplist(#$erlangName{} = Rec) ->");
		$this->append("	lists:zip(record_info(fields, $erlangName), tl(tuple_to_list(Rec)))");
	}
	
	function writeService(DOMElement $serviceNode, $serviceName = null, $serviceId = null, $actionPrefix = "", $extends = "BorhanServiceBase")
	{
		$serviceId = $serviceId ? $serviceId : $serviceNode->getAttribute("id");
		if(!$this->shouldIncludeService($serviceId))
		    return false;

    	$this->startNewTextBlock();
    	
		$serviceName = $serviceName ? $serviceName : $serviceNode->getAttribute("name");
		
		$serviceClassName = "borhan_" . $this->camelCaseToUnderscoreAndLower($serviceName) . "_service";
		$this->appendLine();
		
		if($this->generateDocs)
		{
			$this->appendLine(" % Service $serviceName");
		}
		
		$this->appendLine("-module($serviceClassName).");
		$this->appendLine();
		
		$actionNodes = $serviceNode->getElementsByTagName("action");
		foreach($actionNodes as $actionNode)
		{
			if(!$this->shouldIncludeAction($serviceId, $actionNode->getAttribute("name")))
				continue;
			
            $this->writeActionExport($actionNode);
		}
		
		$this->appendLine('-include_lib("src/borhan_client.hrl").');
		$this->appendLine();
		
		$actionNodes = $serviceNode->getElementsByTagName("action");
		foreach($actionNodes as $actionNode)
		{
            $this->writeAction($serviceId, $actionNode);
		}

    	$this->addFile("src/services/$serviceClassName.erl", $this->getTextBlock());		
	}
	
	function writeActionExport(DOMElement $actionNode)
	{
		$actionName = $actionNode->getAttribute("name");
		
		if(in_array($actionName, $this->reservedActionNames))
			$actionName .= 'Action';
	
	    $resultNode = $actionNode->getElementsByTagName("result")->item(0);
	    $resultType = $resultNode->getAttribute("type");
	    if($resultType == 'file') // TODO add support for serve actions
	    	return;
	    	
		$params = $actionNode->getElementsByTagName("param");
		foreach($params as $paramNode)
		{
			$paramType = $paramNode->getAttribute("type");
		    if($paramType == 'file') // TODO add support for file inputs
		    	return;
		}
		
		$min = 0;
		$max = $params->length;
		while($min < $max)
		{
			$param = $params->item($min);
			/* @var $param DOMElement */
			if(!$param->hasAttribute('optional') || !$param->getAttribute('optional'))
			{
				$min++;
			}
			else
			{
				break;
			}
		}
		
		$signitures = array();
		for($i = $min; $i <= $max; $i++)
		{
			$args = $i + 2; // additional two arguments for ClientConfiguration and ClientRequest
			$signitures[] = "$actionName/$args";
		}
		
		$this->appendLine("-export([" . implode(', ', $signitures) . "]).");
	}
	
	function writeAction($serviceId, DOMElement $actionNode)
	{
		$actionId = $actionNode->getAttribute("name");
		if(!$this->shouldIncludeAction($serviceId, $actionId))
			return;
		
		$actionId = $actionNode->getAttribute("name");
		$actionName = $actionId;
		
		if(in_array($actionName, $this->reservedActionNames))
			$actionName .= 'Action';
		
	    $resultNode = $actionNode->getElementsByTagName("result")->item(0);
	    $resultType = $resultNode->getAttribute("type");
	    if($resultType == 'file') // TODO add support for serve actions
	    	return;
	    	
	    $resultType = $this->getErlangTypeOrRecord($resultType, $resultNode->getAttribute("arrayType"), $resultNode->getAttribute("enumType"));
			
		$paramNodes = $actionNode->getElementsByTagName("param");
		
		foreach($paramNodes as $paramNode)
		{
			$paramType = $paramNode->getAttribute("type");
		    if($paramType == 'file') // TODO add support for file inputs
		    	return;
		}
		
		// -spec declerations
		$arguments = '';
		$params = array();
		$this->appendLine();
		$isFirst = true;
		while(count($params) < $paramNodes->length)
		{
			$param = $paramNodes->item(count($params));
			
			/* @var $param DOMElement */
			if(!$param->hasAttribute('optional') || !$param->getAttribute('optional'))
			{
				$params[] = $param;
				continue;
			}
			
			if(!$isFirst)
			{
				$this->appendLine(".");
			}
				
			if(count($params))
			{
				$arguments = $this->getSpecSignature($params);
				$this->append("-spec $actionName(ClientConfiguration::#borhan_configuration{}, ClientRequest::#borhan_request{}, $arguments)");
			}
			else
			{
				$this->append("-spec $actionName(ClientConfiguration::#borhan_configuration{}, ClientRequest::#borhan_request{})");
			}
			if($resultType)
			{
				$this->append(" -> Result::{$resultType}");
			}
			else
			{
				$this->append(" -> Result::void()");
			}
		
			$params[] = $param;
			$isFirst = false;
		}
	
		if(!$isFirst)
		{
			$this->appendLine(".");
		}
		
		if(count($params))
		{
			$arguments = $this->getSpecSignature($params);
			$this->append("-spec $actionName(ClientConfiguration::#borhan_configuration{}, ClientRequest::#borhan_request{}, $arguments)");
		}
		else
		{
			$this->append("-spec $actionName(ClientConfiguration::#borhan_configuration{}, ClientRequest::#borhan_request{})");
		}
		if($resultType)
		{
			$this->append(" -> Result::{$resultType}");
		}
		else
		{
			$this->append(" -> Result::void()");
		}
		$this->appendLine(".");
		$this->appendLine();
		
	
		
		// function implementation:
		$params = array();
		while(count($params) < $paramNodes->length)
		{
			$param = $paramNodes->item(count($params));
			
			/* @var $param DOMElement */
			if(!$param->hasAttribute('optional') || !$param->getAttribute('optional'))
			{
				$params[] = $param;
				continue;
			}
			
			$optionalParams = array();
			for($i = count($params); $i < $paramNodes->length; $i++)
				$optionalParams[] = $paramNodes->item($i);
				
			$callArguments = 'ClientConfiguration, ClientRequest, ';
			if(count($params))
			{
				$arguments = $this->getSignature($params);
				$callArguments .= $this->getCallSignature($params) . ', ';
				$this->append("$actionName(#borhan_configuration{}=ClientConfiguration, #borhan_request{}=ClientRequest, $arguments)");
			}
			else
			{
				$this->append("$actionName(#borhan_configuration{}=ClientConfiguration, #borhan_request{}=ClientRequest)");
			}
			$this->appendLine(" -> ");
			
			$defaultValue = $param->getAttribute('default');
			if(!is_numeric($defaultValue) && $defaultValue != 'null')
				$defaultValue = '<<"' . $defaultValue . '">>';
			$callArguments .= $defaultValue;
			$this->appendLine("	$actionName($callArguments).");
		
			$params[] = $param;
		}
	
		if(count($params))
		{
			$arguments = $this->getSignature($params);
			$this->appendLine("$actionName(#borhan_configuration{}=ClientConfiguration, #borhan_request{}=ClientRequest, $arguments) ->");
		}
		else
		{
			$this->appendLine("$actionName(#borhan_configuration{}=ClientConfiguration, #borhan_request{}=ClientRequest) ->");
		}
		$paramsCounter = 1;
		$this->appendLine("	Params{$paramsCounter} = [");
		$this->appendLine("		{service, <<\"$serviceId\">>},");
		$this->appendLine("		{action, <<\"$actionId\">>}");
		$this->appendLine("	],");
	
		foreach($paramNodes as $paramNode)
		{
			$nextParamsCounter = $paramsCounter + 1;
			$restName = $paramNode->getAttribute("name");
			$paramName = ucfirst($this->getErlangPropetryName($restName));
			$this->appendLine("	Params{$nextParamsCounter} = borhan_client:add_params(Params{$paramsCounter}, $restName, $paramName),");
			$paramsCounter = $nextParamsCounter;
		}
		$this->appendLine();
		
		if($resultType)
		{
			$this->appendLine("	Results = borhan_client:request(ClientConfiguration, ClientRequest, Params{$paramsCounter}),");
			if($resultType[0] == '#')
			{
				$recordName = preg_replace(array('/^#/', '/\{\}$/'), array('', ''), $resultType);
				$this->appendLine("	list_to_tuple([$recordName | Results]).");
			}
			else
			{
				$this->appendLine("	Results.");
			}
		}
		else
		{
			$this->appendLine("	borhan_client:request(ClientConfiguration, ClientRequest, Params{$paramsCounter}),");
			$this->appendLine("	void.");
		}
	}
	
	/**
	 * @param array<DOMElement> $paramNodes
	 */
	function getSpecSignature($paramNodes)
	{
		$arguments = array();
		
		foreach($paramNodes as $paramNode)
		{
			$paramName = ucfirst($paramNode->getAttribute("name"));
			$paramType = $this->getErlangTypeOrRecord($paramNode->getAttribute("type"), $paramNode->getAttribute("arrayType"), $paramNode->getAttribute("enumType"));
						
			$arguments[] = "{$paramName}::{$paramType}";
		}
		
		return implode(', ', $arguments);
	}
	
	/**
	 * @param array<DOMElement> $paramNodes
	 */
	function getSignature($paramNodes)
	{
		$arguments = array();
		
		foreach($paramNodes as $paramNode)
		{
			$paramName = ucfirst($paramNode->getAttribute("name"));
			$paramType = $this->getErlangTypeOrRecord($paramNode->getAttribute("type"), $paramNode->getAttribute("arrayType"), $paramNode->getAttribute("enumType"));
			if($paramType[0] == '#')
				$paramName = "$paramType=$paramName";
			$arguments[] = $paramName;
		}
		
		return implode(', ', $arguments);
	}
	
	/**
	 * @param array<DOMElement> $paramNodes
	 */
	function getCallSignature($paramNodes)
	{
		$arguments = array();
		
		foreach($paramNodes as $paramNode)
		{
			$paramName = ucfirst($paramNode->getAttribute("name"));
			$arguments[] = $paramName;
		}
		
		return implode(', ', $arguments);
	}
	
	function writeRequestProperties(DOMNodeList $configurationNodes)
	{
		$isFirst = true;
		foreach($configurationNodes as $configurationNode)
		{
			/* @var $configurationNode DOMElement */
			$configurationName = $configurationNode->nodeName;
			$title = "$configurationName properties:";
		
			foreach($configurationNode->childNodes as $configurationPropertyNode)
			{
				/* @var $configurationPropertyNode DOMElement */
				
				if ($configurationPropertyNode->nodeType != XML_ELEMENT_NODE)
					continue;

				if(!$isFirst)
				{
					$this->appendLine(",");
				}
				
				if($title)
				{
					$this->appendLine();
					$this->appendLine("	% $title");
					$title = null;
				}
				
				$configurationProperty = $configurationPropertyNode->localName;
				$type = $this->getErlangTypeOrRecord($configurationPropertyNode->getAttribute("type"), $configurationPropertyNode->getAttribute("arrayType"), $configurationPropertyNode->getAttribute("enumType"));
			
				$this->appendLine();
				if($configurationPropertyNode->hasAttribute('description'))
				{
					$description = $configurationPropertyNode->getAttribute('description');
					$this->appendLine("	% $description");
				}
				
				$value = 'null';
				if($configurationProperty == 'clientTag')
				{
					$date = date('y-m-d');
					$value = "<<\"erlang:$date\">>";
				}
				if($configurationProperty == 'apiVersion')
				{
					$value = '<<"' . $this->_doc->documentElement->getAttribute('apiVersion') . '">>';
				}
				
				$this->append("	$configurationProperty = $value :: $type");
				$isFirst = false;
			}
		}
		$this->appendLine();
	}
	
	protected function addFile($fileName, $fileContents, $addLicense = true)
	{
		$patterns = array(
			'/@package\s+.+/',
			'/@subpackage\s+.+/',
		);
		$replacements = array(
			'@package ' . $this->package,
			'@subpackage ' . $this->subpackage,
		);
		$fileContents = preg_replace($patterns, $replacements, $fileContents);
		parent::addFile($fileName, $fileContents, $addLicense);
	}
	
	public function getPHPType($propType)
	{		
		switch ($propType) 
		{	
			case "bigint" :
				return "int";
				
			case "bool" :
				return "boolean";
				
			default :
				return $propType;
		}
	}
}
