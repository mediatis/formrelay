


## Configure TYPO3 Mail Form 
	postProcessor {
		2 = Mediatis\Formrelay\Plugins\MailFormPostProcessor
		2 {
		}
	}


## Configure Formhandler

	plugin.Tx_Formhandler.settings.predef.FORMNAME {
		...
		finishers {
			1 {
				class = Mediatis\Formrelay\Plugins\FormhandlerFinisher
			}
		}
	}

