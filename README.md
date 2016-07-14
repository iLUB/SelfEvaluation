# SelfEvaluation Plugin Installation

## Requirements

1. ilias 5.0 or higher
2. Library ilubFieldDefinition
3. Library InputGUIs
4. Library Export


## Installation of the iLubFiledDefinition Library

Be sure to have the iLubFieldDefinition Library installed in ILIAS library folder. If it is not installed yet, follow the steps below in order to install it.

Go to yourILIASInstallation/Customizing/global/plugins/Libraries

    git clone git@github.com:iLUB/iLubFieldDefinition.git

Then checkout the correct branch of iLubFieldDefinition otherwise you will run into errors.


## Installation of the InputGUIs Library

Be sure to have the InputGUIs Library installed in ILIAS library folder. If it is not installed yet, follow the steps below in order to install it.

Go to yourILIASInstallation/Customizing/global/plugins/Libraries

    git clone git@github.com:iLUB/InputGUIs.git

Then checkout the correct branch of InputGUIs otherwise you will run into errors.


## Installation of the Export Library

Be sure to have the Export Library installed in ILIAS library folder. If it is not installed yet, follow the steps below in order to install it.

Go to yourILIASInstallation/Customizing/global/plugins/Libraries

    git clone git@github.com:iLUB/Export.git

Then checkout the correct branch of Export otherwise you will run into errors.


## Installation of the SelfEvaluation Plugin

Clone the SelfEvaluation repository to (or create folder path if it does not exist): Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/
	
	git clone git@github.com:iLUB/SelfEvaluation.git

You have a new folder SelfEvaluation. Cd into that folder and be sure to check out the correct branch.

In ILIAS go to Administration -> Plugins then click on the Actions button to the right of the plugin. Update the plugin first, after that activate it through the Actions button.

You can now add the SelfEvaluation to your course. 

