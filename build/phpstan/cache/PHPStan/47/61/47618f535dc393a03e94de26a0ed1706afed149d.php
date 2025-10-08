<?php declare(strict_types = 1);

// odsl-/Users/drewroberts/Code/social/app
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v1',
   'data' => 
  array (
    '/Users/drewroberts/Code/social/app/Contracts/SocialAccountService.php' => 
    array (
      0 => '5a47f12e08612d217291e44f64d80b47a348a3de',
      1 => 
      array (
        0 => 'app\\contracts\\socialaccountservice',
      ),
      2 => 
      array (
        0 => 'app\\contracts\\initiateoauth',
        1 => 'app\\contracts\\handlecallback',
        2 => 'app\\contracts\\refreshtoken',
        3 => 'app\\contracts\\post',
        4 => 'app\\contracts\\verifycredentials',
        5 => 'app\\contracts\\disconnect',
      ),
      3 => 
      array (
      ),
    ),
    '/Users/drewroberts/Code/social/app/Providers/AppServiceProvider.php' => 
    array (
      0 => '01bf9e5cf5bb666446625056b618445ae4749675',
      1 => 
      array (
        0 => 'app\\providers\\appserviceprovider',
      ),
      2 => 
      array (
        0 => 'app\\providers\\register',
        1 => 'app\\providers\\boot',
      ),
      3 => 
      array (
      ),
    ),
    '/Users/drewroberts/Code/social/app/Providers/FortifyServiceProvider.php' => 
    array (
      0 => 'dd4c174b3c3ad6f63e32e41307001eeb3f3b093e',
      1 => 
      array (
        0 => 'app\\providers\\fortifyserviceprovider',
      ),
      2 => 
      array (
        0 => 'app\\providers\\register',
        1 => 'app\\providers\\boot',
      ),
      3 => 
      array (
      ),
    ),
    '/Users/drewroberts/Code/social/app/Providers/Filament/AdminPanelProvider.php' => 
    array (
      0 => 'cd3333c382ac1982ffba1641f24d97e5b286aa6e',
      1 => 
      array (
        0 => 'app\\providers\\filament\\adminpanelprovider',
      ),
      2 => 
      array (
        0 => 'app\\providers\\filament\\panel',
      ),
      3 => 
      array (
      ),
    ),
    '/Users/drewroberts/Code/social/app/Enums/AllowedEmailDomain.php' => 
    array (
      0 => '4496bbff1bfe071f9249f7584f83179d539acf75',
      1 => 
      array (
        0 => 'app\\enums\\allowedemaildomain',
      ),
      2 => 
      array (
        0 => 'app\\enums\\values',
        1 => 'app\\enums\\isallowed',
      ),
      3 => 
      array (
      ),
    ),
    '/Users/drewroberts/Code/social/app/Enums/SocialService.php' => 
    array (
      0 => '523706e3fb9494d684346cf404839faabf0d3e33',
      1 => 
      array (
        0 => 'app\\enums\\socialservice',
      ),
      2 => 
      array (
        0 => 'app\\enums\\values',
        1 => 'app\\enums\\label',
        2 => 'app\\enums\\icon',
        3 => 'app\\enums\\usesoauth1',
        4 => 'app\\enums\\usesoauth2',
      ),
      3 => 
      array (
      ),
    ),
    '/Users/drewroberts/Code/social/app/Models/User.php' => 
    array (
      0 => '7c83e202db73ea7682b9329ec61c01e6e787d373',
      1 => 
      array (
        0 => 'app\\models\\user',
      ),
      2 => 
      array (
        0 => 'app\\models\\casts',
        1 => 'app\\models\\initials',
        2 => 'app\\models\\accounts',
        3 => 'app\\models\\activeaccounts',
      ),
      3 => 
      array (
      ),
    ),
    '/Users/drewroberts/Code/social/app/Models/Account.php' => 
    array (
      0 => '0d1d200153ec374fd5191cb7b9b99ac9329b38c3',
      1 => 
      array (
        0 => 'app\\models\\account',
      ),
      2 => 
      array (
        0 => 'app\\models\\user',
        1 => 'app\\models\\istokenexpired',
        2 => 'app\\models\\needstokenrefresh',
        3 => 'app\\models\\displayname',
        4 => 'app\\models\\scopeactive',
        5 => 'app\\models\\scopeforservice',
      ),
      3 => 
      array (
      ),
    ),
    '/Users/drewroberts/Code/social/app/Models/Purge.php' => 
    array (
      0 => '2786d4f77acc791e2a97525ee039d2b5d8fff076',
      1 => 
      array (
        0 => 'app\\models\\purge',
      ),
      2 => 
      array (
        0 => 'app\\models\\account',
        1 => 'app\\models\\scopepending',
        2 => 'app\\models\\scopepurged',
        3 => 'app\\models\\scopesaved',
        4 => 'app\\models\\scoperequested',
        5 => 'app\\models\\getstatusattribute',
      ),
      3 => 
      array (
      ),
    ),
    '/Users/drewroberts/Code/social/app/Policies/PurgePolicy.php' => 
    array (
      0 => 'd8528efef0647ac0982f7c4cc257da73b634d2b8',
      1 => 
      array (
        0 => 'app\\policies\\purgepolicy',
      ),
      2 => 
      array (
        0 => 'app\\policies\\viewany',
        1 => 'app\\policies\\view',
        2 => 'app\\policies\\create',
        3 => 'app\\policies\\update',
        4 => 'app\\policies\\delete',
        5 => 'app\\policies\\restore',
        6 => 'app\\policies\\forcedelete',
      ),
      3 => 
      array (
      ),
    ),
    '/Users/drewroberts/Code/social/app/Policies/AccountPolicy.php' => 
    array (
      0 => '65237f236ed4ed5d9560a6658b9bc4cfa5dcf9d6',
      1 => 
      array (
        0 => 'app\\policies\\accountpolicy',
      ),
      2 => 
      array (
        0 => 'app\\policies\\viewany',
        1 => 'app\\policies\\view',
        2 => 'app\\policies\\create',
        3 => 'app\\policies\\update',
        4 => 'app\\policies\\delete',
        5 => 'app\\policies\\restore',
        6 => 'app\\policies\\forcedelete',
      ),
      3 => 
      array (
      ),
    ),
    '/Users/drewroberts/Code/social/app/Livewire/Settings/TwoFactor.php' => 
    array (
      0 => 'f911669ebdfa567d4eb1ba6287651e0a59135142',
      1 => 
      array (
        0 => 'app\\livewire\\settings\\twofactor',
      ),
      2 => 
      array (
        0 => 'app\\livewire\\settings\\mount',
        1 => 'app\\livewire\\settings\\enable',
        2 => 'app\\livewire\\settings\\loadsetupdata',
        3 => 'app\\livewire\\settings\\showverificationifnecessary',
        4 => 'app\\livewire\\settings\\confirmtwofactor',
        5 => 'app\\livewire\\settings\\resetverification',
        6 => 'app\\livewire\\settings\\disable',
        7 => 'app\\livewire\\settings\\closemodal',
        8 => 'app\\livewire\\settings\\getmodalconfigproperty',
      ),
      3 => 
      array (
      ),
    ),
    '/Users/drewroberts/Code/social/app/Livewire/Settings/DeleteUserForm.php' => 
    array (
      0 => 'f031570ff8a145c694d1655c6b742f096f62cb44',
      1 => 
      array (
        0 => 'app\\livewire\\settings\\deleteuserform',
      ),
      2 => 
      array (
        0 => 'app\\livewire\\settings\\deleteuser',
      ),
      3 => 
      array (
      ),
    ),
    '/Users/drewroberts/Code/social/app/Livewire/Settings/TwoFactor/RecoveryCodes.php' => 
    array (
      0 => 'cf1a7f8bf44eb2376026d2a263814009adfddfd1',
      1 => 
      array (
        0 => 'app\\livewire\\settings\\twofactor\\recoverycodes',
      ),
      2 => 
      array (
        0 => 'app\\livewire\\settings\\twofactor\\mount',
        1 => 'app\\livewire\\settings\\twofactor\\regeneraterecoverycodes',
        2 => 'app\\livewire\\settings\\twofactor\\loadrecoverycodes',
      ),
      3 => 
      array (
      ),
    ),
    '/Users/drewroberts/Code/social/app/Livewire/Settings/Password.php' => 
    array (
      0 => '0527f76bfd807ffe4028b708d268ba48708a92b7',
      1 => 
      array (
        0 => 'app\\livewire\\settings\\password',
      ),
      2 => 
      array (
        0 => 'app\\livewire\\settings\\updatepassword',
      ),
      3 => 
      array (
      ),
    ),
    '/Users/drewroberts/Code/social/app/Livewire/Settings/Profile.php' => 
    array (
      0 => '6706d82b14e6ca6ca3f7e2b686d6440f65aa95d3',
      1 => 
      array (
        0 => 'app\\livewire\\settings\\profile',
      ),
      2 => 
      array (
        0 => 'app\\livewire\\settings\\mount',
        1 => 'app\\livewire\\settings\\updateprofileinformation',
        2 => 'app\\livewire\\settings\\resendverificationnotification',
      ),
      3 => 
      array (
      ),
    ),
    '/Users/drewroberts/Code/social/app/Livewire/Settings/Appearance.php' => 
    array (
      0 => '6fbb9bfca9350a6b36bff7fbd11e3cf320f97139',
      1 => 
      array (
        0 => 'app\\livewire\\settings\\appearance',
      ),
      2 => 
      array (
      ),
      3 => 
      array (
      ),
    ),
    '/Users/drewroberts/Code/social/app/Livewire/Auth/Login.php' => 
    array (
      0 => '335c8274793cd4b7fc01ba4f681350882d7e8a89',
      1 => 
      array (
        0 => 'app\\livewire\\auth\\login',
      ),
      2 => 
      array (
        0 => 'app\\livewire\\auth\\login',
        1 => 'app\\livewire\\auth\\validatecredentials',
        2 => 'app\\livewire\\auth\\ensureisnotratelimited',
        3 => 'app\\livewire\\auth\\throttlekey',
      ),
      3 => 
      array (
      ),
    ),
    '/Users/drewroberts/Code/social/app/Livewire/Auth/Register.php' => 
    array (
      0 => '4e0a07b924b1e309e924018dd96f5d6ea120800c',
      1 => 
      array (
        0 => 'app\\livewire\\auth\\register',
      ),
      2 => 
      array (
        0 => 'app\\livewire\\auth\\register',
      ),
      3 => 
      array (
      ),
    ),
    '/Users/drewroberts/Code/social/app/Livewire/Auth/ResetPassword.php' => 
    array (
      0 => '5d80cc51fd80d08ed26d6536200c505ac774eafd',
      1 => 
      array (
        0 => 'app\\livewire\\auth\\resetpassword',
      ),
      2 => 
      array (
        0 => 'app\\livewire\\auth\\mount',
        1 => 'app\\livewire\\auth\\resetpassword',
      ),
      3 => 
      array (
      ),
    ),
    '/Users/drewroberts/Code/social/app/Livewire/Auth/VerifyEmail.php' => 
    array (
      0 => 'bff000b1edd4930c6f10fbc2a0aaeb7a2d800400',
      1 => 
      array (
        0 => 'app\\livewire\\auth\\verifyemail',
      ),
      2 => 
      array (
        0 => 'app\\livewire\\auth\\sendverification',
        1 => 'app\\livewire\\auth\\logout',
      ),
      3 => 
      array (
      ),
    ),
    '/Users/drewroberts/Code/social/app/Livewire/Auth/ForgotPassword.php' => 
    array (
      0 => 'e67e115052b2180329c95425e0c323e24a6ed5dd',
      1 => 
      array (
        0 => 'app\\livewire\\auth\\forgotpassword',
      ),
      2 => 
      array (
        0 => 'app\\livewire\\auth\\sendpasswordresetlink',
      ),
      3 => 
      array (
      ),
    ),
    '/Users/drewroberts/Code/social/app/Livewire/Actions/Logout.php' => 
    array (
      0 => 'fe9543b019689b4a8fa205e076872705870845c8',
      1 => 
      array (
        0 => 'app\\livewire\\actions\\logout',
      ),
      2 => 
      array (
        0 => 'app\\livewire\\actions\\__invoke',
      ),
      3 => 
      array (
      ),
    ),
    '/Users/drewroberts/Code/social/app/Filament/Resources/Accounts/Tables/AccountsTable.php' => 
    array (
      0 => '923eeb001aa659a44b0afd5f4aea60d613b0f093',
      1 => 
      array (
        0 => 'app\\filament\\resources\\accounts\\tables\\accountstable',
      ),
      2 => 
      array (
        0 => 'app\\filament\\resources\\accounts\\tables\\configure',
      ),
      3 => 
      array (
      ),
    ),
    '/Users/drewroberts/Code/social/app/Filament/Resources/Accounts/AccountResource.php' => 
    array (
      0 => '26c012049061bb50829d9f9d125e2d42f8fda171',
      1 => 
      array (
        0 => 'app\\filament\\resources\\accounts\\accountresource',
      ),
      2 => 
      array (
        0 => 'app\\filament\\resources\\accounts\\form',
        1 => 'app\\filament\\resources\\accounts\\table',
        2 => 'app\\filament\\resources\\accounts\\getrelations',
        3 => 'app\\filament\\resources\\accounts\\getpages',
      ),
      3 => 
      array (
      ),
    ),
    '/Users/drewroberts/Code/social/app/Filament/Resources/Accounts/Schemas/AccountForm.php' => 
    array (
      0 => '0062d6c8488f68e8fe4c54a9485d6084266f5479',
      1 => 
      array (
        0 => 'app\\filament\\resources\\accounts\\schemas\\accountform',
      ),
      2 => 
      array (
        0 => 'app\\filament\\resources\\accounts\\schemas\\configure',
      ),
      3 => 
      array (
      ),
    ),
    '/Users/drewroberts/Code/social/app/Filament/Resources/Accounts/Pages/ViewAccount.php' => 
    array (
      0 => 'b511923547bdc610d285dcfde5f7bd2d82f6ffc7',
      1 => 
      array (
        0 => 'app\\filament\\resources\\accounts\\pages\\viewaccount',
      ),
      2 => 
      array (
        0 => 'app\\filament\\resources\\accounts\\pages\\getheaderactions',
      ),
      3 => 
      array (
      ),
    ),
    '/Users/drewroberts/Code/social/app/Filament/Resources/Accounts/Pages/ListAccounts.php' => 
    array (
      0 => 'bba37fabaeaa0e60160885a282a4d430dd1fa0a2',
      1 => 
      array (
        0 => 'app\\filament\\resources\\accounts\\pages\\listaccounts',
      ),
      2 => 
      array (
        0 => 'app\\filament\\resources\\accounts\\pages\\getheaderactions',
      ),
      3 => 
      array (
      ),
    ),
    '/Users/drewroberts/Code/social/app/Filament/Resources/Users/Tables/UsersTable.php' => 
    array (
      0 => '6ae528412ff0ead0e8406d6e11ae99177351a6f3',
      1 => 
      array (
        0 => 'app\\filament\\resources\\users\\tables\\userstable',
      ),
      2 => 
      array (
        0 => 'app\\filament\\resources\\users\\tables\\configure',
      ),
      3 => 
      array (
      ),
    ),
    '/Users/drewroberts/Code/social/app/Filament/Resources/Users/UserResource.php' => 
    array (
      0 => 'e77204b48907b8af4ac9a6515d0a956fa80a4e0f',
      1 => 
      array (
        0 => 'app\\filament\\resources\\users\\userresource',
      ),
      2 => 
      array (
        0 => 'app\\filament\\resources\\users\\form',
        1 => 'app\\filament\\resources\\users\\table',
        2 => 'app\\filament\\resources\\users\\getrelations',
        3 => 'app\\filament\\resources\\users\\getpages',
      ),
      3 => 
      array (
      ),
    ),
    '/Users/drewroberts/Code/social/app/Filament/Resources/Users/Schemas/UserForm.php' => 
    array (
      0 => '639be4500f09251e3e838e257c0f36e0e7efa531',
      1 => 
      array (
        0 => 'app\\filament\\resources\\users\\schemas\\userform',
      ),
      2 => 
      array (
        0 => 'app\\filament\\resources\\users\\schemas\\configure',
      ),
      3 => 
      array (
      ),
    ),
    '/Users/drewroberts/Code/social/app/Filament/Resources/Users/Pages/ListUsers.php' => 
    array (
      0 => '82bf1e7bd37ec269bdc595864fd035cf6bc5c34f',
      1 => 
      array (
        0 => 'app\\filament\\resources\\users\\pages\\listusers',
      ),
      2 => 
      array (
        0 => 'app\\filament\\resources\\users\\pages\\getheaderactions',
      ),
      3 => 
      array (
      ),
    ),
    '/Users/drewroberts/Code/social/app/Filament/Resources/Users/Pages/EditUser.php' => 
    array (
      0 => 'c403f563c824379ba3cde06e5822fbdbdd1de328',
      1 => 
      array (
        0 => 'app\\filament\\resources\\users\\pages\\edituser',
      ),
      2 => 
      array (
        0 => 'app\\filament\\resources\\users\\pages\\getheaderactions',
      ),
      3 => 
      array (
      ),
    ),
    '/Users/drewroberts/Code/social/app/Filament/Resources/Users/Pages/CreateUser.php' => 
    array (
      0 => '06fe4dbf3e8a5297d19b323fbb04a06de7705a70',
      1 => 
      array (
        0 => 'app\\filament\\resources\\users\\pages\\createuser',
      ),
      2 => 
      array (
      ),
      3 => 
      array (
      ),
    ),
    '/Users/drewroberts/Code/social/app/Filament/Resources/Purges/Tables/PurgesTable.php' => 
    array (
      0 => '567451f17e9aa886ea53819c208645d8d592242f',
      1 => 
      array (
        0 => 'app\\filament\\resources\\purges\\tables\\purgestable',
      ),
      2 => 
      array (
        0 => 'app\\filament\\resources\\purges\\tables\\configure',
      ),
      3 => 
      array (
      ),
    ),
    '/Users/drewroberts/Code/social/app/Filament/Resources/Purges/PurgeResource.php' => 
    array (
      0 => '7eb2e0cbea39df21feb9e00216eac0cc7df5688a',
      1 => 
      array (
        0 => 'app\\filament\\resources\\purges\\purgeresource',
      ),
      2 => 
      array (
        0 => 'app\\filament\\resources\\purges\\form',
        1 => 'app\\filament\\resources\\purges\\table',
        2 => 'app\\filament\\resources\\purges\\getrelations',
        3 => 'app\\filament\\resources\\purges\\getpages',
      ),
      3 => 
      array (
      ),
    ),
    '/Users/drewroberts/Code/social/app/Filament/Resources/Purges/Schemas/PurgeForm.php' => 
    array (
      0 => 'ed2cfa4df71df367b81ebfab78e240c5aae14d59',
      1 => 
      array (
        0 => 'app\\filament\\resources\\purges\\schemas\\purgeform',
      ),
      2 => 
      array (
        0 => 'app\\filament\\resources\\purges\\schemas\\configure',
      ),
      3 => 
      array (
      ),
    ),
    '/Users/drewroberts/Code/social/app/Filament/Resources/Purges/Pages/ViewPurge.php' => 
    array (
      0 => '9d9aaea627b67907060527ba670d75423e473b9e',
      1 => 
      array (
        0 => 'app\\filament\\resources\\purges\\pages\\viewpurge',
      ),
      2 => 
      array (
        0 => 'app\\filament\\resources\\purges\\pages\\getheaderactions',
      ),
      3 => 
      array (
      ),
    ),
    '/Users/drewroberts/Code/social/app/Filament/Resources/Purges/Pages/ListPurges.php' => 
    array (
      0 => 'dee9cf6bb090b33e793af17a2c6919fa7ff10b24',
      1 => 
      array (
        0 => 'app\\filament\\resources\\purges\\pages\\listpurges',
      ),
      2 => 
      array (
        0 => 'app\\filament\\resources\\purges\\pages\\getheaderactions',
      ),
      3 => 
      array (
      ),
    ),
    '/Users/drewroberts/Code/social/app/Http/Requests/StoreAccountRequest.php' => 
    array (
      0 => 'ce7c6e28079070f5e4bda31312c21866d5147b45',
      1 => 
      array (
        0 => 'app\\http\\requests\\storeaccountrequest',
      ),
      2 => 
      array (
        0 => 'app\\http\\requests\\authorize',
        1 => 'app\\http\\requests\\rules',
      ),
      3 => 
      array (
      ),
    ),
    '/Users/drewroberts/Code/social/app/Http/Requests/StorePurgeRequest.php' => 
    array (
      0 => 'f7246f8d7fc07254df7536c384c9c3e96803ad1f',
      1 => 
      array (
        0 => 'app\\http\\requests\\storepurgerequest',
      ),
      2 => 
      array (
        0 => 'app\\http\\requests\\authorize',
        1 => 'app\\http\\requests\\rules',
      ),
      3 => 
      array (
      ),
    ),
    '/Users/drewroberts/Code/social/app/Http/Requests/UpdateAccountRequest.php' => 
    array (
      0 => '99c1241cb6b26e9406f1d06f3fef2a646f8f5cff',
      1 => 
      array (
        0 => 'app\\http\\requests\\updateaccountrequest',
      ),
      2 => 
      array (
        0 => 'app\\http\\requests\\authorize',
        1 => 'app\\http\\requests\\rules',
      ),
      3 => 
      array (
      ),
    ),
    '/Users/drewroberts/Code/social/app/Http/Requests/UpdatePurgeRequest.php' => 
    array (
      0 => '77782424314df67c46a3524071d38d8440851037',
      1 => 
      array (
        0 => 'app\\http\\requests\\updatepurgerequest',
      ),
      2 => 
      array (
        0 => 'app\\http\\requests\\authorize',
        1 => 'app\\http\\requests\\rules',
      ),
      3 => 
      array (
      ),
    ),
    '/Users/drewroberts/Code/social/app/Http/Controllers/Controller.php' => 
    array (
      0 => 'a33a5105f92c73a309c9f8a549905dcdf6dccbae',
      1 => 
      array (
        0 => 'app\\http\\controllers\\controller',
      ),
      2 => 
      array (
      ),
      3 => 
      array (
      ),
    ),
    '/Users/drewroberts/Code/social/app/Http/Controllers/Auth/VerifyEmailController.php' => 
    array (
      0 => '6cc9eaffee6aaa637af4d9f87fa3744fc260ce7f',
      1 => 
      array (
        0 => 'app\\http\\controllers\\auth\\verifyemailcontroller',
      ),
      2 => 
      array (
        0 => 'app\\http\\controllers\\auth\\__invoke',
      ),
      3 => 
      array (
      ),
    ),
    '/Users/drewroberts/Code/social/app/Http/Controllers/AccountController.php' => 
    array (
      0 => 'e645682e936c2129129542c21b192785203c0a97',
      1 => 
      array (
        0 => 'app\\http\\controllers\\accountcontroller',
      ),
      2 => 
      array (
        0 => 'app\\http\\controllers\\index',
        1 => 'app\\http\\controllers\\create',
        2 => 'app\\http\\controllers\\store',
        3 => 'app\\http\\controllers\\show',
        4 => 'app\\http\\controllers\\edit',
        5 => 'app\\http\\controllers\\update',
        6 => 'app\\http\\controllers\\destroy',
      ),
      3 => 
      array (
      ),
    ),
    '/Users/drewroberts/Code/social/app/Http/Controllers/PurgeController.php' => 
    array (
      0 => 'bd578156bca7a1b414c0896ef371fe63459419fc',
      1 => 
      array (
        0 => 'app\\http\\controllers\\purgecontroller',
      ),
      2 => 
      array (
        0 => 'app\\http\\controllers\\index',
        1 => 'app\\http\\controllers\\create',
        2 => 'app\\http\\controllers\\store',
        3 => 'app\\http\\controllers\\show',
        4 => 'app\\http\\controllers\\edit',
        5 => 'app\\http\\controllers\\update',
        6 => 'app\\http\\controllers\\destroy',
      ),
      3 => 
      array (
      ),
    ),
    '/Users/drewroberts/Code/social/app/Http/Controllers/SocialAuthController.php' => 
    array (
      0 => '964fbda742f062d3b46206454c32a207dcea2cad',
      1 => 
      array (
        0 => 'app\\http\\controllers\\socialauthcontroller',
      ),
      2 => 
      array (
        0 => 'app\\http\\controllers\\connect',
        1 => 'app\\http\\controllers\\callback',
        2 => 'app\\http\\controllers\\disconnect',
      ),
      3 => 
      array (
      ),
    ),
    '/Users/drewroberts/Code/social/app/Services/PurgeService.php' => 
    array (
      0 => '4be2f286ecfb2bd4263e09c9e2187f20b27df073',
      1 => 
      array (
        0 => 'app\\services\\purgeservice',
      ),
      2 => 
      array (
        0 => 'app\\services\\__construct',
        1 => 'app\\services\\getdefaultaccount',
        2 => 'app\\services\\processpurge',
        3 => 'app\\services\\getnextpendingpurge',
        4 => 'app\\services\\getstats',
      ),
      3 => 
      array (
      ),
    ),
    '/Users/drewroberts/Code/social/app/Services/TwitterAccountService.php' => 
    array (
      0 => '41b650071828e7ee0f6db831dfdc7283de93eeda',
      1 => 
      array (
        0 => 'app\\services\\twitteraccountservice',
      ),
      2 => 
      array (
        0 => 'app\\services\\initiateoauth',
        1 => 'app\\services\\handlecallback',
        2 => 'app\\services\\refreshtoken',
        3 => 'app\\services\\post',
        4 => 'app\\services\\verifycredentials',
        5 => 'app\\services\\deletetweet',
        6 => 'app\\services\\disconnect',
      ),
      3 => 
      array (
      ),
    ),
    '/Users/drewroberts/Code/social/app/Console/Commands/ProcessPurgeQueue.php' => 
    array (
      0 => 'daf3590e79bb028c55f2b6c6addd6af2986e1a83',
      1 => 
      array (
        0 => 'app\\console\\commands\\processpurgequeue',
      ),
      2 => 
      array (
        0 => 'app\\console\\commands\\handle',
      ),
      3 => 
      array (
      ),
    ),
    '/Users/drewroberts/Code/social/app/Filament/Widgets/PurgeBatchSaveWidget.php' => 
    array (
      0 => '390a4fd31b7a2270504b7dd3c8b09ac45e79a126',
      1 => 
      array (
        0 => 'app\\filament\\widgets\\purgebatchsavewidget',
      ),
      2 => 
      array (
        0 => 'app\\filament\\widgets\\getsearchcount',
        1 => 'app\\filament\\widgets\\batchsave',
      ),
      3 => 
      array (
      ),
    ),
    '/Users/drewroberts/Code/social/app/Console/Commands/PurgeBatchSave.php' => 
    array (
      0 => '8bd770cd8b31cfce90db086590211442f8889622',
      1 => 
      array (
        0 => 'app\\console\\commands\\purgebatchsave',
      ),
      2 => 
      array (
        0 => 'app\\console\\commands\\handle',
      ),
      3 => 
      array (
      ),
    ),
    '/Users/drewroberts/Code/social/app/Console/Commands/PurgeBatchUnsave.php' => 
    array (
      0 => '62497ca5223af990fb6b0e1a784f5916d3c6be7e',
      1 => 
      array (
        0 => 'app\\console\\commands\\purgebatchunsave',
      ),
      2 => 
      array (
        0 => 'app\\console\\commands\\handle',
      ),
      3 => 
      array (
      ),
    ),
  ),
));