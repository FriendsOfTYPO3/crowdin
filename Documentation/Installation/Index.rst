..  include:: /Includes.rst.txt

..  _installation:

============
Installation
============

..  important::

    You will need `an account at Crowdin`_ to get efficient utilisation of this
    extension. An account gives you the role as translator per default.

..  _an account at Crowdin: https://accounts.crowdin.com/register


#.  Using composer

    #. `composer require friendsoftypo3/crowdin`.
    #. `./vendor/bin/typo3 crowdin:enable`.

#.  Non composer

    #. Download the extension from TER.
    #. `./typo3/sysext/core/bin/typo3 crowdin:enable`.

Additional information
======================

The ``enable`` command above writes the following information to
:file:`config/system/settings.php`:

..  code-block:: php

    $GLOBALS['TYPO3_CONF_VARS']['SYS']['localization']['locales']['user']['t3'] = 'Crowdin In-Context Localization';
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['fluid']['namespaces'] = [
        'f' => [
            'TYPO3\\CMS\\Fluid\\ViewHelpers',
            'TYPO3Fluid\\Fluid\\ViewHelpers',
            'FriendsOfTYPO3\\Crowdin\\ViewHelpers\\Override\\V14',
        ],
    ];

.. note::

   If you are running TYPO3 v12 or v13, change

   ``FriendsOfTYPO3\\Crowdin\\ViewHelpers\\Override\\V14``

   into:

   ``FriendsOfTYPO3\\Crowdin\\ViewHelpers\\Override\\V12``
