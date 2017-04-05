<?php

use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\RequestContext;

/**
 * appSecure_basicDebugProjectContainerUrlMatcher.
 *
 * This class has been auto-generated
 * by the Symfony Routing Component.
 */
class appSecure_basicDebugProjectContainerUrlMatcher extends Symfony\Bundle\FrameworkBundle\Routing\RedirectableUrlMatcher
{
    /**
     * Constructor.
     */
    public function __construct(RequestContext $context)
    {
        $this->context = $context;
    }

    public function match($pathinfo)
    {
        $allow = array();
        $pathinfo = rawurldecode($pathinfo);
        $context = $this->context;
        $request = $this->request;

        if (0 === strpos($pathinfo, '/v2')) {
            if (0 === strpos($pathinfo, '/v2/pet')) {
                // swagger.petstore.pet.updatePet
                if ($pathinfo === '/v2/pet') {
                    if ($this->context->getMethod() != 'PUT') {
                        $allow[] = 'PUT';
                        goto not_swaggerpetstorepetupdatePet;
                    }

                    $requiredSchemes = array (  'http' => 0,);
                    if (!isset($requiredSchemes[$this->context->getScheme()])) {
                        return $this->redirect($pathinfo, 'swagger.petstore.pet.updatePet', key($requiredSchemes));
                    }

                    return array (  '_controller' => 'swagger.controller.pet:updatePet',  '_swagger.uri' => 'swagger/petstore.yml',  '_swagger.path' => '/pet',  '_route' => 'swagger.petstore.pet.updatePet',);
                }
                not_swaggerpetstorepetupdatePet:

                // swagger.petstore.pet.addPet
                if ($pathinfo === '/v2/pet') {
                    if ($this->context->getMethod() != 'POST') {
                        $allow[] = 'POST';
                        goto not_swaggerpetstorepetaddPet;
                    }

                    $requiredSchemes = array (  'http' => 0,);
                    if (!isset($requiredSchemes[$this->context->getScheme()])) {
                        return $this->redirect($pathinfo, 'swagger.petstore.pet.addPet', key($requiredSchemes));
                    }

                    return array (  '_controller' => 'swagger.controller.pet:addPet',  '_swagger.uri' => 'swagger/petstore.yml',  '_swagger.path' => '/pet',  '_route' => 'swagger.petstore.pet.addPet',);
                }
                not_swaggerpetstorepetaddPet:

                if (0 === strpos($pathinfo, '/v2/pet/findBy')) {
                    // swagger.petstore.pet.findbystatus.findPetsByStatus
                    if ($pathinfo === '/v2/pet/findByStatus') {
                        if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                            $allow = array_merge($allow, array('GET', 'HEAD'));
                            goto not_swaggerpetstorepetfindbystatusfindPetsByStatus;
                        }

                        $requiredSchemes = array (  'http' => 0,);
                        if (!isset($requiredSchemes[$this->context->getScheme()])) {
                            return $this->redirect($pathinfo, 'swagger.petstore.pet.findbystatus.findPetsByStatus', key($requiredSchemes));
                        }

                        return array (  '_controller' => 'swagger.controller.pet:findPetsByStatus',  '_swagger.uri' => 'swagger/petstore.yml',  '_swagger.path' => '/pet/findByStatus',  '_route' => 'swagger.petstore.pet.findbystatus.findPetsByStatus',);
                    }
                    not_swaggerpetstorepetfindbystatusfindPetsByStatus:

                    // swagger.petstore.pet.findbytags.findPetsByTags
                    if ($pathinfo === '/v2/pet/findByTags') {
                        if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                            $allow = array_merge($allow, array('GET', 'HEAD'));
                            goto not_swaggerpetstorepetfindbytagsfindPetsByTags;
                        }

                        $requiredSchemes = array (  'http' => 0,);
                        if (!isset($requiredSchemes[$this->context->getScheme()])) {
                            return $this->redirect($pathinfo, 'swagger.petstore.pet.findbytags.findPetsByTags', key($requiredSchemes));
                        }

                        return array (  '_controller' => 'swagger.controller.pet:findPetsByTags',  '_swagger.uri' => 'swagger/petstore.yml',  '_swagger.path' => '/pet/findByTags',  '_route' => 'swagger.petstore.pet.findbytags.findPetsByTags',);
                    }
                    not_swaggerpetstorepetfindbytagsfindPetsByTags:

                }

                // swagger.petstore.pet.petid.getPetById
                if (preg_match('#^/v2/pet/(?P<petId>\\d+)$#s', $pathinfo, $matches)) {
                    if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                        $allow = array_merge($allow, array('GET', 'HEAD'));
                        goto not_swaggerpetstorepetpetidgetPetById;
                    }

                    $requiredSchemes = array (  'http' => 0,);
                    if (!isset($requiredSchemes[$this->context->getScheme()])) {
                        return $this->redirect($pathinfo, 'swagger.petstore.pet.petid.getPetById', key($requiredSchemes));
                    }

                    return $this->mergeDefaults(array_replace($matches, array('_route' => 'swagger.petstore.pet.petid.getPetById')), array (  '_controller' => 'swagger.controller.pet:getPetById',  '_swagger.uri' => 'swagger/petstore.yml',  '_swagger.path' => '/pet/{petId}',));
                }
                not_swaggerpetstorepetpetidgetPetById:

                // swagger.petstore.pet.petid.updatePetWithForm
                if (preg_match('#^/v2/pet/(?P<petId>\\d+)$#s', $pathinfo, $matches)) {
                    if ($this->context->getMethod() != 'POST') {
                        $allow[] = 'POST';
                        goto not_swaggerpetstorepetpetidupdatePetWithForm;
                    }

                    $requiredSchemes = array (  'http' => 0,);
                    if (!isset($requiredSchemes[$this->context->getScheme()])) {
                        return $this->redirect($pathinfo, 'swagger.petstore.pet.petid.updatePetWithForm', key($requiredSchemes));
                    }

                    return $this->mergeDefaults(array_replace($matches, array('_route' => 'swagger.petstore.pet.petid.updatePetWithForm')), array (  '_controller' => 'swagger.controller.pet:updatePetWithForm',  '_swagger.uri' => 'swagger/petstore.yml',  '_swagger.path' => '/pet/{petId}',));
                }
                not_swaggerpetstorepetpetidupdatePetWithForm:

                // swagger.petstore.pet.petid.deletePet
                if (preg_match('#^/v2/pet/(?P<petId>\\d+)$#s', $pathinfo, $matches)) {
                    if ($this->context->getMethod() != 'DELETE') {
                        $allow[] = 'DELETE';
                        goto not_swaggerpetstorepetpetiddeletePet;
                    }

                    $requiredSchemes = array (  'http' => 0,);
                    if (!isset($requiredSchemes[$this->context->getScheme()])) {
                        return $this->redirect($pathinfo, 'swagger.petstore.pet.petid.deletePet', key($requiredSchemes));
                    }

                    return $this->mergeDefaults(array_replace($matches, array('_route' => 'swagger.petstore.pet.petid.deletePet')), array (  '_controller' => 'swagger.controller.pet:deletePet',  '_swagger.uri' => 'swagger/petstore.yml',  '_swagger.path' => '/pet/{petId}',));
                }
                not_swaggerpetstorepetpetiddeletePet:

                // swagger.petstore.pet.petid.uploadimage.uploadFile
                if (preg_match('#^/v2/pet/(?P<petId>\\d+)/uploadImage$#s', $pathinfo, $matches)) {
                    if ($this->context->getMethod() != 'POST') {
                        $allow[] = 'POST';
                        goto not_swaggerpetstorepetpetiduploadimageuploadFile;
                    }

                    $requiredSchemes = array (  'http' => 0,);
                    if (!isset($requiredSchemes[$this->context->getScheme()])) {
                        return $this->redirect($pathinfo, 'swagger.petstore.pet.petid.uploadimage.uploadFile', key($requiredSchemes));
                    }

                    return $this->mergeDefaults(array_replace($matches, array('_route' => 'swagger.petstore.pet.petid.uploadimage.uploadFile')), array (  '_controller' => 'swagger.controller.pet:uploadFile',  '_swagger.uri' => 'swagger/petstore.yml',  '_swagger.path' => '/pet/{petId}/uploadImage',));
                }
                not_swaggerpetstorepetpetiduploadimageuploadFile:

            }

            if (0 === strpos($pathinfo, '/v2/store')) {
                // swagger.petstore.store.inventory.getInventory
                if ($pathinfo === '/v2/store/inventory') {
                    if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                        $allow = array_merge($allow, array('GET', 'HEAD'));
                        goto not_swaggerpetstorestoreinventorygetInventory;
                    }

                    $requiredSchemes = array (  'http' => 0,);
                    if (!isset($requiredSchemes[$this->context->getScheme()])) {
                        return $this->redirect($pathinfo, 'swagger.petstore.store.inventory.getInventory', key($requiredSchemes));
                    }

                    return array (  '_controller' => 'swagger.controller.store:getInventory',  '_swagger.uri' => 'swagger/petstore.yml',  '_swagger.path' => '/store/inventory',  '_route' => 'swagger.petstore.store.inventory.getInventory',);
                }
                not_swaggerpetstorestoreinventorygetInventory:

                if (0 === strpos($pathinfo, '/v2/store/order')) {
                    // swagger.petstore.store.order.placeOrder
                    if ($pathinfo === '/v2/store/order') {
                        if ($this->context->getMethod() != 'POST') {
                            $allow[] = 'POST';
                            goto not_swaggerpetstorestoreorderplaceOrder;
                        }

                        $requiredSchemes = array (  'http' => 0,);
                        if (!isset($requiredSchemes[$this->context->getScheme()])) {
                            return $this->redirect($pathinfo, 'swagger.petstore.store.order.placeOrder', key($requiredSchemes));
                        }

                        return array (  '_controller' => 'swagger.controller.store:placeOrder',  '_swagger.uri' => 'swagger/petstore.yml',  '_swagger.path' => '/store/order',  '_route' => 'swagger.petstore.store.order.placeOrder',);
                    }
                    not_swaggerpetstorestoreorderplaceOrder:

                    // swagger.petstore.store.order.orderid.getOrderById
                    if (preg_match('#^/v2/store/order/(?P<orderId>\\d+)$#s', $pathinfo, $matches)) {
                        if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                            $allow = array_merge($allow, array('GET', 'HEAD'));
                            goto not_swaggerpetstorestoreorderorderidgetOrderById;
                        }

                        $requiredSchemes = array (  'http' => 0,);
                        if (!isset($requiredSchemes[$this->context->getScheme()])) {
                            return $this->redirect($pathinfo, 'swagger.petstore.store.order.orderid.getOrderById', key($requiredSchemes));
                        }

                        return $this->mergeDefaults(array_replace($matches, array('_route' => 'swagger.petstore.store.order.orderid.getOrderById')), array (  '_controller' => 'swagger.controller.store:getOrderById',  '_swagger.uri' => 'swagger/petstore.yml',  '_swagger.path' => '/store/order/{orderId}',));
                    }
                    not_swaggerpetstorestoreorderorderidgetOrderById:

                    // swagger.petstore.store.order.orderid.deleteOrder
                    if (preg_match('#^/v2/store/order/(?P<orderId>[^/]++)$#s', $pathinfo, $matches)) {
                        if ($this->context->getMethod() != 'DELETE') {
                            $allow[] = 'DELETE';
                            goto not_swaggerpetstorestoreorderorderiddeleteOrder;
                        }

                        $requiredSchemes = array (  'http' => 0,);
                        if (!isset($requiredSchemes[$this->context->getScheme()])) {
                            return $this->redirect($pathinfo, 'swagger.petstore.store.order.orderid.deleteOrder', key($requiredSchemes));
                        }

                        return $this->mergeDefaults(array_replace($matches, array('_route' => 'swagger.petstore.store.order.orderid.deleteOrder')), array (  '_controller' => 'swagger.controller.store:deleteOrder',  '_swagger.uri' => 'swagger/petstore.yml',  '_swagger.path' => '/store/order/{orderId}',));
                    }
                    not_swaggerpetstorestoreorderorderiddeleteOrder:

                }

            }

            if (0 === strpos($pathinfo, '/v2/user')) {
                // swagger.petstore.user.createUser
                if ($pathinfo === '/v2/user') {
                    if ($this->context->getMethod() != 'POST') {
                        $allow[] = 'POST';
                        goto not_swaggerpetstoreusercreateUser;
                    }

                    $requiredSchemes = array (  'http' => 0,);
                    if (!isset($requiredSchemes[$this->context->getScheme()])) {
                        return $this->redirect($pathinfo, 'swagger.petstore.user.createUser', key($requiredSchemes));
                    }

                    return array (  '_controller' => 'swagger.controller.user:createUser',  '_swagger.uri' => 'swagger/petstore.yml',  '_swagger.path' => '/user',  '_route' => 'swagger.petstore.user.createUser',);
                }
                not_swaggerpetstoreusercreateUser:

                if (0 === strpos($pathinfo, '/v2/user/createWith')) {
                    // swagger.petstore.user.createwitharray.createUsersWithArrayInput
                    if ($pathinfo === '/v2/user/createWithArray') {
                        if ($this->context->getMethod() != 'POST') {
                            $allow[] = 'POST';
                            goto not_swaggerpetstoreusercreatewitharraycreateUsersWithArrayInput;
                        }

                        $requiredSchemes = array (  'http' => 0,);
                        if (!isset($requiredSchemes[$this->context->getScheme()])) {
                            return $this->redirect($pathinfo, 'swagger.petstore.user.createwitharray.createUsersWithArrayInput', key($requiredSchemes));
                        }

                        return array (  '_controller' => 'swagger.controller.user:createUsersWithArrayInput',  '_swagger.uri' => 'swagger/petstore.yml',  '_swagger.path' => '/user/createWithArray',  '_route' => 'swagger.petstore.user.createwitharray.createUsersWithArrayInput',);
                    }
                    not_swaggerpetstoreusercreatewitharraycreateUsersWithArrayInput:

                    // swagger.petstore.user.createwithlist.createUsersWithListInput
                    if ($pathinfo === '/v2/user/createWithList') {
                        if ($this->context->getMethod() != 'POST') {
                            $allow[] = 'POST';
                            goto not_swaggerpetstoreusercreatewithlistcreateUsersWithListInput;
                        }

                        $requiredSchemes = array (  'http' => 0,);
                        if (!isset($requiredSchemes[$this->context->getScheme()])) {
                            return $this->redirect($pathinfo, 'swagger.petstore.user.createwithlist.createUsersWithListInput', key($requiredSchemes));
                        }

                        return array (  '_controller' => 'swagger.controller.user:createUsersWithListInput',  '_swagger.uri' => 'swagger/petstore.yml',  '_swagger.path' => '/user/createWithList',  '_route' => 'swagger.petstore.user.createwithlist.createUsersWithListInput',);
                    }
                    not_swaggerpetstoreusercreatewithlistcreateUsersWithListInput:

                }

                if (0 === strpos($pathinfo, '/v2/user/log')) {
                    // swagger.petstore.user.login.loginUser
                    if ($pathinfo === '/v2/user/login') {
                        if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                            $allow = array_merge($allow, array('GET', 'HEAD'));
                            goto not_swaggerpetstoreuserloginloginUser;
                        }

                        $requiredSchemes = array (  'http' => 0,);
                        if (!isset($requiredSchemes[$this->context->getScheme()])) {
                            return $this->redirect($pathinfo, 'swagger.petstore.user.login.loginUser', key($requiredSchemes));
                        }

                        return array (  '_controller' => 'swagger.controller.user:loginUser',  '_swagger.uri' => 'swagger/petstore.yml',  '_swagger.path' => '/user/login',  '_route' => 'swagger.petstore.user.login.loginUser',);
                    }
                    not_swaggerpetstoreuserloginloginUser:

                    // swagger.petstore.user.logout.logoutUser
                    if ($pathinfo === '/v2/user/logout') {
                        if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                            $allow = array_merge($allow, array('GET', 'HEAD'));
                            goto not_swaggerpetstoreuserlogoutlogoutUser;
                        }

                        $requiredSchemes = array (  'http' => 0,);
                        if (!isset($requiredSchemes[$this->context->getScheme()])) {
                            return $this->redirect($pathinfo, 'swagger.petstore.user.logout.logoutUser', key($requiredSchemes));
                        }

                        return array (  '_controller' => 'swagger.controller.user:logoutUser',  '_swagger.uri' => 'swagger/petstore.yml',  '_swagger.path' => '/user/logout',  '_route' => 'swagger.petstore.user.logout.logoutUser',);
                    }
                    not_swaggerpetstoreuserlogoutlogoutUser:

                }

                // swagger.petstore.user.username.getUserByName
                if (preg_match('#^/v2/user/(?P<username>[^/]++)$#s', $pathinfo, $matches)) {
                    if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                        $allow = array_merge($allow, array('GET', 'HEAD'));
                        goto not_swaggerpetstoreuserusernamegetUserByName;
                    }

                    $requiredSchemes = array (  'http' => 0,);
                    if (!isset($requiredSchemes[$this->context->getScheme()])) {
                        return $this->redirect($pathinfo, 'swagger.petstore.user.username.getUserByName', key($requiredSchemes));
                    }

                    return $this->mergeDefaults(array_replace($matches, array('_route' => 'swagger.petstore.user.username.getUserByName')), array (  '_controller' => 'swagger.controller.user:getUserByName',  '_swagger.uri' => 'swagger/petstore.yml',  '_swagger.path' => '/user/{username}',));
                }
                not_swaggerpetstoreuserusernamegetUserByName:

                // swagger.petstore.user.username.updateUser
                if (preg_match('#^/v2/user/(?P<username>[^/]++)$#s', $pathinfo, $matches)) {
                    if ($this->context->getMethod() != 'PUT') {
                        $allow[] = 'PUT';
                        goto not_swaggerpetstoreuserusernameupdateUser;
                    }

                    $requiredSchemes = array (  'http' => 0,);
                    if (!isset($requiredSchemes[$this->context->getScheme()])) {
                        return $this->redirect($pathinfo, 'swagger.petstore.user.username.updateUser', key($requiredSchemes));
                    }

                    return $this->mergeDefaults(array_replace($matches, array('_route' => 'swagger.petstore.user.username.updateUser')), array (  '_controller' => 'swagger.controller.user:updateUser',  '_swagger.uri' => 'swagger/petstore.yml',  '_swagger.path' => '/user/{username}',));
                }
                not_swaggerpetstoreuserusernameupdateUser:

                // swagger.petstore.user.username.deleteUser
                if (preg_match('#^/v2/user/(?P<username>[^/]++)$#s', $pathinfo, $matches)) {
                    if ($this->context->getMethod() != 'DELETE') {
                        $allow[] = 'DELETE';
                        goto not_swaggerpetstoreuserusernamedeleteUser;
                    }

                    $requiredSchemes = array (  'http' => 0,);
                    if (!isset($requiredSchemes[$this->context->getScheme()])) {
                        return $this->redirect($pathinfo, 'swagger.petstore.user.username.deleteUser', key($requiredSchemes));
                    }

                    return $this->mergeDefaults(array_replace($matches, array('_route' => 'swagger.petstore.user.username.deleteUser')), array (  '_controller' => 'swagger.controller.user:deleteUser',  '_swagger.uri' => 'swagger/petstore.yml',  '_swagger.path' => '/user/{username}',));
                }
                not_swaggerpetstoreuserusernamedeleteUser:

            }

        }

        if (0 === strpos($pathinfo, '/data/v1/entity')) {
            // swagger.data.entity.status.getStatus
            if ($pathinfo === '/data/v1/entity/status') {
                if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                    $allow = array_merge($allow, array('GET', 'HEAD'));
                    goto not_swaggerdataentitystatusgetStatus;
                }

                return array (  '_controller' => 'swagger.controller.entity:getStatus',  '_swagger.uri' => 'swagger/data.yml',  '_swagger.path' => '/entity/status',  '_route' => 'swagger.data.entity.status.getStatus',);
            }
            not_swaggerdataentitystatusgetStatus:

            // swagger.data.entity.type.find
            if (preg_match('#^/data/v1/entity/(?P<type>[^/]++)$#s', $pathinfo, $matches)) {
                if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                    $allow = array_merge($allow, array('GET', 'HEAD'));
                    goto not_swaggerdataentitytypefind;
                }

                return $this->mergeDefaults(array_replace($matches, array('_route' => 'swagger.data.entity.type.find')), array (  '_controller' => 'swagger.controller.entity:find',  '_swagger.uri' => 'swagger/data.yml',  '_swagger.path' => '/entity/{type}',));
            }
            not_swaggerdataentitytypefind:

            // swagger.data.entity.type.post
            if (preg_match('#^/data/v1/entity/(?P<type>[^/]++)$#s', $pathinfo, $matches)) {
                if ($this->context->getMethod() != 'POST') {
                    $allow[] = 'POST';
                    goto not_swaggerdataentitytypepost;
                }

                return $this->mergeDefaults(array_replace($matches, array('_route' => 'swagger.data.entity.type.post')), array (  '_controller' => 'swagger.controller.entity:post',  '_swagger.uri' => 'swagger/data.yml',  '_swagger.path' => '/entity/{type}',));
            }
            not_swaggerdataentitytypepost:

            // swagger.data.entity.type.findbycriteria.findByCriteria
            if (preg_match('#^/data/v1/entity/(?P<type>[^/]++)/findByCriteria$#s', $pathinfo, $matches)) {
                if ($this->context->getMethod() != 'POST') {
                    $allow[] = 'POST';
                    goto not_swaggerdataentitytypefindbycriteriafindByCriteria;
                }

                return $this->mergeDefaults(array_replace($matches, array('_route' => 'swagger.data.entity.type.findbycriteria.findByCriteria')), array (  '_controller' => 'swagger.controller.entity:findByCriteria',  '_swagger.uri' => 'swagger/data.yml',  '_swagger.path' => '/entity/{type}/findByCriteria',));
            }
            not_swaggerdataentitytypefindbycriteriafindByCriteria:

            // swagger.data.entity.type.id.get
            if (preg_match('#^/data/v1/entity/(?P<type>[^/]++)/(?P<id>\\d+)$#s', $pathinfo, $matches)) {
                if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                    $allow = array_merge($allow, array('GET', 'HEAD'));
                    goto not_swaggerdataentitytypeidget;
                }

                return $this->mergeDefaults(array_replace($matches, array('_route' => 'swagger.data.entity.type.id.get')), array (  '_controller' => 'swagger.controller.entity:get',  '_swagger.uri' => 'swagger/data.yml',  '_swagger.path' => '/entity/{type}/{id}',));
            }
            not_swaggerdataentitytypeidget:

            // swagger.data.entity.type.id.put
            if (preg_match('#^/data/v1/entity/(?P<type>[^/]++)/(?P<id>\\d+)$#s', $pathinfo, $matches)) {
                if ($this->context->getMethod() != 'PUT') {
                    $allow[] = 'PUT';
                    goto not_swaggerdataentitytypeidput;
                }

                return $this->mergeDefaults(array_replace($matches, array('_route' => 'swagger.data.entity.type.id.put')), array (  '_controller' => 'swagger.controller.entity:put',  '_swagger.uri' => 'swagger/data.yml',  '_swagger.path' => '/entity/{type}/{id}',));
            }
            not_swaggerdataentitytypeidput:

            // swagger.data.entity.type.id.delete
            if (preg_match('#^/data/v1/entity/(?P<type>[^/]++)/(?P<id>\\d+)$#s', $pathinfo, $matches)) {
                if ($this->context->getMethod() != 'DELETE') {
                    $allow[] = 'DELETE';
                    goto not_swaggerdataentitytypeiddelete;
                }

                return $this->mergeDefaults(array_replace($matches, array('_route' => 'swagger.data.entity.type.id.delete')), array (  '_controller' => 'swagger.controller.entity:delete',  '_swagger.uri' => 'swagger/data.yml',  '_swagger.path' => '/entity/{type}/{id}',));
            }
            not_swaggerdataentitytypeiddelete:

        }

        if (0 === strpos($pathinfo, '/basic-auth/v1')) {
            // swagger.basic_auth.secure.secure
            if ($pathinfo === '/basic-auth/v1/secure') {
                if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                    $allow = array_merge($allow, array('GET', 'HEAD'));
                    goto not_swaggerbasic_authsecuresecure;
                }

                $requiredSchemes = array (  'http' => 0,);
                if (!isset($requiredSchemes[$this->context->getScheme()])) {
                    return $this->redirect($pathinfo, 'swagger.basic_auth.secure.secure', key($requiredSchemes));
                }

                return array (  '_controller' => 'swagger.controller.secured:secure',  '_swagger.uri' => 'swagger/basic_auth.yml',  '_swagger.path' => '/secure',  '_route' => 'swagger.basic_auth.secure.secure',);
            }
            not_swaggerbasic_authsecuresecure:

            if (0 === strpos($pathinfo, '/basic-auth/v1/rbac-')) {
                // swagger.basic_auth.rbac.user.rbacUser
                if ($pathinfo === '/basic-auth/v1/rbac-user') {
                    if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                        $allow = array_merge($allow, array('GET', 'HEAD'));
                        goto not_swaggerbasic_authrbacuserrbacUser;
                    }

                    $requiredSchemes = array (  'http' => 0,);
                    if (!isset($requiredSchemes[$this->context->getScheme()])) {
                        return $this->redirect($pathinfo, 'swagger.basic_auth.rbac.user.rbacUser', key($requiredSchemes));
                    }

                    return array (  '_controller' => 'swagger.controller.secured:rbacUser',  '_swagger.uri' => 'swagger/basic_auth.yml',  '_swagger.path' => '/rbac-user',  '_route' => 'swagger.basic_auth.rbac.user.rbacUser',);
                }
                not_swaggerbasic_authrbacuserrbacUser:

                // swagger.basic_auth.rbac.admin.rbacAdmin
                if ($pathinfo === '/basic-auth/v1/rbac-admin') {
                    if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                        $allow = array_merge($allow, array('GET', 'HEAD'));
                        goto not_swaggerbasic_authrbacadminrbacAdmin;
                    }

                    $requiredSchemes = array (  'http' => 0,);
                    if (!isset($requiredSchemes[$this->context->getScheme()])) {
                        return $this->redirect($pathinfo, 'swagger.basic_auth.rbac.admin.rbacAdmin', key($requiredSchemes));
                    }

                    return array (  '_controller' => 'swagger.controller.secured:rbacAdmin',  '_swagger.uri' => 'swagger/basic_auth.yml',  '_swagger.path' => '/rbac-admin',  '_route' => 'swagger.basic_auth.rbac.admin.rbacAdmin',);
                }
                not_swaggerbasic_authrbacadminrbacAdmin:

            }

            // swagger.basic_auth.unsecured.unsecured
            if ($pathinfo === '/basic-auth/v1/unsecured') {
                if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                    $allow = array_merge($allow, array('GET', 'HEAD'));
                    goto not_swaggerbasic_authunsecuredunsecured;
                }

                $requiredSchemes = array (  'http' => 0,);
                if (!isset($requiredSchemes[$this->context->getScheme()])) {
                    return $this->redirect($pathinfo, 'swagger.basic_auth.unsecured.unsecured', key($requiredSchemes));
                }

                return array (  '_controller' => 'swagger.controller.secured:unsecured',  '_swagger.uri' => 'swagger/basic_auth.yml',  '_swagger.path' => '/unsecured',  '_route' => 'swagger.basic_auth.unsecured.unsecured',);
            }
            not_swaggerbasic_authunsecuredunsecured:

        }

        throw 0 < count($allow) ? new MethodNotAllowedException(array_unique($allow)) : new ResourceNotFoundException();
    }
}
