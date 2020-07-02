# SelfEvaluation Plugin Installation

## Requirements

1. ilias 5.2 or higher

## Installation of the SelfEvaluation Plugin

Clone the SelfEvaluation repository to (or create folder path if it does not exist): Customizing/global/plugins/Services/Repository/RepositoryObject/
	
	git clone https://github.com/iLUB/SelfEvaluation.git

You have a new folder SelfEvaluation. Cd into that folder and be sure to check out the correct branch.

In ILIAS go to Administration -> Plugins then click on the Actions button to the right of the plugin. Update the plugin first, after that activate it through the Actions button.

You can now add the SelfEvaluation to your course. 

Todo: add the following lines to checkAccessMobUsage of 
Services/MediaObjects/classes/class.ilObjMediaObjectAccess.php

`//Self Evaluation, visible with read access on instance
case "xsev:html":
          return $this->access->checkAccess("read","", $usage["id"]);
          break;`