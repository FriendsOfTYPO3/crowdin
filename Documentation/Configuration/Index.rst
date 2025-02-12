..  include:: /Includes.rst.txt

..  _configuration:

=============
Configuration
=============


#.  Log into backend and download at least two language packs from :guilabel:`ADMIN TOOLS > Maintenance > Manage Language Packs` :

    #. The language you want to translate to

    #. The language pack **Crowdin In-Context Localization [t3]**

    #. Click :guilabel:`Update all`

#.  Go to your profile **User settings** and select **Crowdin In-Context Localization [t3]** as your Languages

#.  When you click save, TYPO3 will connect to Crowdin and you will be asked to login using your Crowdin Credentials


Translating extensions
======================

You can switch between TYPO3 Core and your local extensions by using
the selector in the TYPO3 top bar.

To be able to translate an extension, it needs to be enabled as a project on
Crowdin.

..  seealso::
    :ref:`known-problems-where-extension-top-bar`


Screenshots
===========

When the things are running, you should have access to the Crowdin Modal Tool in
the bottom of the browser window:

..  figure:: /Images/crowdin-tool.png
    :class: with-shadow
    :alt: Crowdin Tool
    :width: 288px

    Crowdin Tool after installation.

whereas the Crowdin Icon in the TYPO3 top bar will let you enable/disable
in-context localization and switch between translating TYPO3 core or your
favorite extensions:

..  figure:: /Images/crowdin-toolbar.png
    :class: with-shadow
    :alt: Toolbar for Crowdin

    Context menu for Crowdin in the TYPO3 top bar.
