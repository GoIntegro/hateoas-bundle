<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace JsonApi\Request;

// Mocks.
use Codeception\Util\Stub;
// Request.
use GoIntegro\Bundle\HateoasBundle\JsonApi\Merge\Blender;
// Tests.
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;

class BlenderTest extends TestCase
{
    /**
     * @var array
     */
    private static $usersDocument = array (
      'links' =>
      array (
        'users.product' =>
        array (
          'href' => '/api/v2/products/{users.product}',
          'type' => 'products',
        ),
        'users.groups-joined' =>
        array (
          'href' => '/api/v2/users/{users.id}/links/groups-joined',
          'type' => 'groups',
        ),
        'users.followers' =>
        array (
          'href' => '/api/v2/follows?subject={users.id}',
          'type' => 'follows',
        )
      ),
      'users' =>
      array (
        0 =>
        array (
          'id' => '15',
          'type' => 'users',
          'name' => 'John',
          'surname' => 'Connor',
          'email' => NULL,
          'links' =>
          array (
            'product' => '3',
            'groups-joined' =>
            array (
              0 => '13'
            )
          )
        ),
        1 =>
        array (
          'id' => '16',
          'type' => 'users',
          'name' => 'John',
          'surname' => 'Connor',
          'email' => 'johnconnor@gointegro.com',
          'links' =>
          array (
            'product' => '3',
            'groups-joined' =>
            array (
              0 => '13'
            )
          )
        )
      )
    );
    /**
     * @var array
     */
    private static $userDocumentAVersion1 = array (
      'links' =>
      array (
        'users.product' =>
        array (
          'href' => '/api/v2/products/{users.product}',
          'type' => 'products',
        ),
        'users.groups-joined' =>
        array (
          'href' => '/api/v2/users/{users.id}/links/groups-joined',
          'type' => 'groups',
        ),
        'users.followers' =>
        array (
          'href' => '/api/v2/follows?subject={users.id}',
          'type' => 'follows',
        )
      ),
      'users' =>
      array (
          'id' => '15',
          'type' => 'users',
          'name' => 'John', // Not in version 2.
          'surname' => 'Connor', // Not in version 2.
          'links' =>
          array (
            'product' => '3',
            'groups-joined' =>
            array (
              0 => '13'
            )
          )
        )
    );
    /**
     * @var array
     */
    private static $userDocumentAVersion2 = array (
      'links' =>
      array (
        'users.product' =>
        array (
          'href' => '/api/v2/products/{users.product}',
          'type' => 'products',
        ),
        'users.groups-joined' =>
        array (
          'href' => '/api/v2/users/{users.id}/links/groups-joined',
          'type' => 'groups',
        ),
        'users.followers' =>
        array (
          'href' => '/api/v2/follows?subject={users.id}',
          'type' => 'follows',
        )
      ),
      'users' =>
      array (
          'id' => '15',
          'type' => 'users',
          'email' => NULL, // Not in version 1.
          'links' =>
          array (
            'product' => '3',
            'groups-joined' =>
            array (
              0 => '13'
            )
          )
        )
    );
    /**
     * @var array
     */
    private static $productsDocumentVersion1 = array (
      'links' =>
      array (
        'products.account' =>
        array (
          'href' => '/api/v2/accounts/{products.account}',
          'type' => 'accounts'
        ),
        'products.groups' =>
        array (
          'href' => '/api/v2/products/{products.id}/links/groups',
          'type' => 'groups'
        ),
        'products.users' =>
        array (
          'href' => '/api/v2/users?product={products.id}',
          'type' => 'users'
        ),
      ),
      'products' =>
      array (
        0 =>
        array (
          'id' => '3',
          'type' => 'products',
          'name' => 'Hugo',
          'host' => 'hugo.gointegro.net',
          'links' =>
          array (
            'account' => '3',
            'groups' =>
            array (
              0 => '21',
              1 => '22'
            )
          )
        )
      )
    );
    /**
     * @var array
     */
    private static $productsDocumentVersion2 = array (
      'links' =>
      array (
        'products.account' =>
        array (
          'href' => '/api/v2/accounts/{products.account}',
          'type' => 'accounts'
        ),
        'products.groups' =>
        array (
          'href' => '/api/v2/products/{products.id}/links/groups',
          'type' => 'groups'
        ),
        'products.users' =>
        array (
          'href' => '/api/v2/users?product={products.id}',
          'type' => 'users'
        ),
      ),
      'products' =>
      array (
        0 =>
        array (
          'id' => '6',
          'type' => 'products',
          'name' => 'Mario',
          'enabled' => TRUE,
          'links' =>
          array (
            'account' => '3',
            'groups' =>
            array (
              0 => '21',
              1 => '22'
            )
          )
        )
      )
    );
    /**
     * @var array
     */
    private static $productDocumentA = array (
      'links' =>
      array (
        'products.account' =>
        array (
          'href' => '/api/v2/accounts/{products.account}',
          'type' => 'accounts'
        ),
        'products.groups' =>
        array (
          'href' => '/api/v2/products/{products.id}/links/groups',
          'type' => 'groups'
        ),
        'products.users' =>
        array (
          'href' => '/api/v2/users?product={products.id}',
          'type' => 'users'
        ),
      ),
      'products' =>
        array (
          'id' => '3',
          'type' => 'products',
          'name' => 'Hugo',
          'host' => 'product.skqr.gointegro.net', // Not in Platform B.
          'links' =>
          array (
            'account' => '3',
            'groups' =>
            array (
              0 => '21',
              1 => '22'
            )
          )
        )
    );
    /**
     * @var array
     */
    private static $productDocumentB = array (
      'links' =>
      array (
        'products.account' =>
        array (
          'href' => '/api/v2/accounts/{products.account}',
          'type' => 'accounts'
        ),
        'products.groups' =>
        array (
          'href' => '/api/v2/products/{products.id}/links/groups',
          'type' => 'groups'
        ),
        'products.users' =>
        array (
          'href' => '/api/v2/users?product={products.id}',
          'type' => 'users'
        ),
      ),
      'products' =>
        array (
          'id' => '6',
          'type' => 'products',
          'name' => 'Mario',
          'enabled' => TRUE, // Not in Platform A.
          'links' =>
          array (
            'account' => '3',
            'groups' =>
            array (
              0 => '21',
              1 => '22'
            )
          )
        )
    );
    /**
     * @var array
     */
    private static $messagesDocumentWithLinked = array (
      'links' =>
      array (
        'messages.event' =>
        array (
          'href' => '/api/v2/events/{messages.event}',
          'type' => 'events',
        ),
        'messages.recipient-groups' =>
        array (
          'href' => '/api/v2/messages/{messages.id}/links/recipient-groups',
          'type' => 'recipient-groups',
        ),
        'events.message' =>
        array (
          'href' => '/api/v2/messages/{events.message}',
          'type' => 'messages',
        ),
        'events.action' =>
        array (
          'href' => '/api/v2/posts/{events.action}',
          'type' => 'posts',
        ),
        'messages:first' =>
        array (
          'href' => '/api/v2/messages?include=event&page=1&size=2',
          'type' => 'messages',
        ),
        'messages:next' =>
        array (
          'href' => '/api/v2/messages?include=event&page=2&size=2',
          'type' => 'messages',
        ),
        'messages:last' =>
        array (
          'href' => '/api/v2/messages?include=event&page=4&size=2',
          'type' => 'messages',
        ),
      ),
      'messages' =>
      array (
        0 =>
        array (
          'id' => '6',
          'type' => 'messages',
          'links' =>
          array (
            'event' => '6',
            'recipient-groups' =>
            array (
              0 => '16',
              1 => '17',
              2 => '18',
            ),
          ),
        ),
        1 =>
        array (
          'id' => '5',
          'type' => 'messages',
          'links' =>
          array (
            'event' => '5',
            'recipient-groups' =>
            array (
              0 => '13',
              1 => '14',
              2 => '15',
            ),
          ),
        ),
      ),
      'linked' =>
      array (
        'events' =>
        array (
          0 =>
          array (
            'id' => '6',
            'type' => 'events',
            'subtype' => 'post-events',
            'links' =>
            array (
              'message' => '6',
              'action' => '6',
            ),
          ),
          1 =>
          array (
            'id' => '5',
            'type' => 'events',
            'subtype' => 'post-events',
            'links' =>
            array (
              'message' => '5',
              'action' => '5',
            ),
          ),
        ),
      ),
      'meta' =>
      array (
        'messages' =>
        array (
          'pagination' =>
          array (
            'page' => 1,
            'size' => 2,
            'total' => 6,
          ),
        ),
      ),
    );
    /**
     * @var array
     */
    private static $couponsDocumentVersion1 = array (
      'links' =>
      array (
        'coupons.company' =>
        array (
          'href' => '/api/v2/companies/{coupons.company}',
          'type' => 'companies',
        ),
        'coupons.discount' =>
        array (
          'href' => '/api/v2/discounts/{coupons.discount}',
          'type' => 'discounts',
        ),
        'coupons:first' =>
        array (
          'href' => '/api/v2/coupons?q=%2A&page=1&size=2',
          'type' => 'coupons',
        ),
        'coupons:next' =>
        array (
          'href' => '/api/v2/coupons?q=%2A&page=2&size=2',
          'type' => 'coupons',
        ),
        'coupons:last' =>
        array (
          'href' => '/api/v2/coupons?q=%2A&page=6&size=2',
          'type' => 'coupons',
        ),
      ),
      'coupons' =>
      array (
        0 =>
        array (
          'id' => '106',
          'type' => 'coupons',
          'name' => 'elit',
          'links' =>
          array (
            'company' => '95',
            'discount' => '162'
          ),
        ),
        1 =>
        array (
          'id' => '107',
          'type' => 'coupons',
          'name' => 'cillum',
          'links' =>
          array (
            'company' => '93',
            'discount' => '152'
          ),
        ),
      ),
    'meta' =>
      array (
        'coupons' =>
        array (
          'pagination' =>
          array (
            'page' => 1,
            'size' => 2,
            'total' => 10,
          )
        ),
      ),
    );
    /**
     * @var array
     */
    private static $couponsDocumentVersion2 = array (
      'links' =>
      array (
        'coupons.company' =>
        array (
          'href' => '/api/v2/companies/{coupons.company}',
          'type' => 'companies',
        ),
        'coupons.discount' =>
        array (
          'href' => '/api/v2/discounts/{coupons.discount}',
          'type' => 'discounts',
        ),
        'coupons:first' =>
        array (
          'href' => '/api/v2/coupons?q=%2A&page=1&size=2',
          'type' => 'coupons',
        ),
        'coupons:previous' =>
        array (
          'href' => '/api/v2/coupons?q=%2A&page=1&size=2',
          'type' => 'coupons',
        ),
        'coupons:next' =>
        array (
          'href' => '/api/v2/coupons?q=%2A&page=3&size=2',
          'type' => 'coupons',
        ),
        'coupons:last' =>
        array (
          'href' => '/api/v2/coupons?q=%2A&page=6&size=2',
          'type' => 'coupons',
        ),
      ),
      'coupons' =>
      array (
        0 =>
        array (
          'id' => '108',
          'type' => 'coupons',
          'name' => 'elit',
          'links' =>
          array (
            'company' => '95',
            'discount' => '162'
          ),
        ),
        1 =>
        array (
          'id' => '109',
          'type' => 'coupons',
          'name' => 'cillum',
          'links' =>
          array (
            'company' => '93',
            'discount' => '152'
          ),
        ),
      ),
    'meta' =>
      array (
        'coupons' =>
        array (
          'pagination' =>
          array (
            'page' => 2,
            'size' => 2,
            'total' => 10,
          )
        ),
      ),
    );
    /**
     * @var array
     */
    private static $couponsDocumentVersion3 = array (
      'links' =>
      array (
        'coupons.company' =>
        array (
          'href' => '/api/v2/companies/{coupons.company}',
          'type' => 'companies',
        ),
        'coupons.discount' =>
        array (
          'href' => '/api/v2/discounts/{coupons.discount}',
          'type' => 'discounts',
        ),
        'coupons:first' =>
        array (
          'href' => '/api/v2/coupons?q=%2A&page=1&size=2',
          'type' => 'coupons',
        ),
        'coupons:previous' =>
        array (
          'href' => '/api/v2/coupons?q=%2A&page=1&size=2',
          'type' => 'coupons',
        ),
        'coupons:next' =>
        array (
          'href' => '/api/v2/coupons?q=%2A&page=3&size=2',
          'type' => 'coupons',
        ),
        'coupons:last' =>
        array (
          'href' => '/api/v2/coupons?q=%2A&page=6&size=2',
          'type' => 'coupons',
        ),
      ),
      'coupons' =>
      array (
        0 =>
        array (
          'id' => '108',
          'type' => 'coupons',
          'name' => 'elit',
          'links' =>
          array (
            'company' => '95',
            'discount' => '162'
          ),
        ),
        1 =>
        array (
          'id' => '109',
          'type' => 'coupons',
          'name' => 'cillum',
          'links' =>
          array (
            'company' => '93',
            'discount' => '152'
          ),
        ),
      ),
    'meta' =>
      array (
        'coupons' =>
        array (
          'pagination' =>
          array (
            'page' => 2,
            'size' => 2,
            'total' => 10,
          )
        ),
      ),
    );
    /**
     * @var array
     */
    private static $groupsDocumentWithLinked = array (
      'links' =>
      array (
        'groups.owner' =>
        array (
          'href' => '/api/v2/users/{groups.owner}',
          'type' => 'users',
        ),
        'groups.group' =>
        array (
          'href' => '/api/v2/groups/{groups.group}',
          'type' => 'groups',
        ),
        'groups.product' =>
        array (
          'href' => '/api/v2/products/{groups.product}',
          'type' => 'products',
        ),
        'groups.group-applications' =>
        array (
          'href' => '/api/v2/groups/{groups.id}/links/group-applications',
          'type' => 'applications',
        ),
        'groups.users' =>
        array (
          'href' => '/api/v2/groups/{groups.id}/links/users',
          'type' => 'users',
        ),
        'groups.posts' =>
        array (
          'href' => '/api/v2/posts?subject={groups.id}',
          'type' => 'posts',
        ),
        'products.account' =>
        array (
          'href' => '/api/v2/accounts/{products.account}',
          'type' => 'accounts',
        ),
        'products.auth-strategy' =>
        array (
          'href' => '/api/v2/auth-strategies/{products.auth-strategy}',
          'type' => 'auth-strategies',
        ),
        'products.default-language' =>
        array (
          'href' => '/api/v2/languages/{products.default-language}',
          'type' => 'languages',
        ),
        'products.groups' =>
        array (
          'href' => '/api/v2/products/{products.id}/links/groups',
          'type' => 'groups',
        ),
        'products.group-applications' =>
        array (
          'href' => '/api/v2/products/{products.id}/links/group-applications',
          'type' => 'applications',
        ),
        'products.translations' =>
        array (
          'href' => '/api/v2/products/{products.id}/links/translations',
          'type' => 'translations',
        ),
        'products.languages' =>
        array (
          'href' => '/api/v2/products/{products.id}/links/languages',
          'type' => 'languages',
        ),
        'products.users' =>
        array (
          'href' => '/api/v2/users?product={products.id}',
          'type' => 'users',
        ),
        'products.groups' =>
        array (
          'href' => '/api/v2/groups?product={products.id}',
          'type' => 'groups',
        ),
        'products.positions' =>
        array (
          'href' => '/api/v2/positions?product={products.id}',
          'type' => 'positions',
        ),
        'products.groups' =>
        array (
          'href' => '/api/v2/groups?product={products.id}',
          'type' => 'groups',
        ),
        'users.product' =>
        array (
          'href' => '/api/v2/products/{users.product}',
          'type' => 'products',
        ),
        'users.profile' =>
        array (
          'href' => '/api/v2/profiles/{users.profile}',
          'type' => 'profiles',
        ),
        'users.message-preference' =>
        array (
          'href' => '/api/v2/messages/{users.message-preference}',
          'type' => 'messages',
        ),
        'users.chief' =>
        array (
          'href' => '/api/v2/users/{users.chief}',
          'type' => 'users',
        ),
        'users.groups' =>
        array (
          'href' => '/api/v2/users/{users.id}/links/groups',
          'type' => 'groups',
        ),
        'users.groups-joined' =>
        array (
          'href' => '/api/v2/users/{users.id}/links/groups-joined',
          'type' => 'groups',
        ),
        'users.assignees' =>
        array (
          'href' => '/api/v2/users/{users.id}/links/assignees',
          'type' => 'users',
        ),
        'users.followers' =>
        array (
          'href' => '/api/v2/follows?subject={users.id}',
          'type' => 'follows',
        ),
        'groups:first' =>
        array (
          'href' => '/api/v2/groups?fields%5Bproducts%5D=name&fields%5Busers%5D=name&fields%5Bgroups%5D=name&include=product%2Cusers&page=1&size=2',
          'type' => 'groups',
        ),
        'groups:next' =>
        array (
          'href' => '/api/v2/groups?fields%5Bproducts%5D=name&fields%5Busers%5D=name&fields%5Bgroups%5D=name&include=product%2Cusers&page=2&size=2',
          'type' => 'groups',
        ),
        'groups:last' =>
        array (
          'href' => '/api/v2/groups?fields%5Bproducts%5D=name&fields%5Busers%5D=name&fields%5Bgroups%5D=name&include=product%2Cusers&page=4&size=2',
          'type' => 'groups',
        ),
      ),
      'groups' =>
      array (
        0 =>
        array (
          'id' => '13',
          'type' => 'groups',
          'name' => 'Espacio Público',
          'links' =>
          array (
            'owner' => '15',
            'group' => '21',
            'product' => '3',
            'group-applications' =>
            array (
              0 => '17',
              1 => '18',
              2 => '19',
              3 => '20',
              4 => '21',
              5 => '22',
              6 => '24',
            ),
            'users' =>
            array (
              0 => '15',
              1 => '16',
              2 => '17',
              3 => '18',
              4 => '19',
              5 => '20',
              6 => '21',
            ),
          ),
        ),
        1 =>
        array (
          'id' => '14',
          'type' => 'groups',
          'name' => 'Espacio Privado',
          'links' =>
          array (
            'owner' => '15',
            'group' => '21',
            'product' => '3',
            'group-applications' =>
            array (
            ),
            'users' =>
            array (
            ),
          ),
        ),
      ),
      'linked' =>
      array (
        'products' =>
        array (
          0 =>
          array (
            'id' => '3',
            'type' => 'products',
            'name' => 'Hugo',
            'links' =>
            array (
              'account' => '3',
              'auth-strategy' => '3',
              'default-language' => '7',
              'groups' =>
              array (
                0 => '21',
                1 => '22',
                2 => '23',
                3 => '24',
                4 => '25',
                5 => '26',
                6 => '27',
                7 => '28',
                8 => '29',
                9 => '30',
              ),
              'group-applications' =>
              array (
                0 => '17',
                1 => '18',
                2 => '19',
                3 => '20',
                4 => '21',
                5 => '22',
                6 => '23',
                7 => '24',
              ),
              'translations' =>
              array (
              ),
              'languages' =>
              array (
                0 => '7',
                1 => '8',
                2 => '9',
              ),
            ),
          ),
        ),
        'users' =>
        array (
          0 =>
          array (
            'id' => '15',
            'type' => 'users',
            'name' => 'John',
            'links' =>
            array (
              'product' => '3',
              'profile' => '15',
              'message-preference' => '15',
              'chief' => NULL,
              'groups' =>
              array (
                0 => '13',
                1 => '14',
                2 => '15',
                3 => '16',
                4 => '17',
                5 => '18',
              ),
              'groups-joined' =>
              array (
                0 => '13',
              ),
              'assignees' =>
              array (
              ),
            ),
          ),
          1 =>
          array (
            'id' => '16',
            'type' => 'users',
            'name' => 'John',
            'links' =>
            array (
              'product' => '3',
              'profile' => '16',
              'message-preference' => '16',
              'chief' => NULL,
              'groups' =>
              array (
              ),
              'groups-joined' =>
              array (
                0 => '13',
              ),
              'assignees' =>
              array (
              ),
            ),
          ),
          2 =>
          array (
            'id' => '17',
            'type' => 'users',
            'name' => 'John',
            'links' =>
            array (
              'product' => '3',
              'profile' => '17',
              'message-preference' => '17',
              'chief' => NULL,
              'groups' =>
              array (
              ),
              'groups-joined' =>
              array (
                0 => '13',
              ),
              'assignees' =>
              array (
              ),
            ),
          ),
          3 =>
          array (
            'id' => '18',
            'type' => 'users',
            'name' => 'Sarah',
            'links' =>
            array (
              'product' => '3',
              'profile' => '18',
              'message-preference' => '18',
              'chief' => NULL,
              'groups' =>
              array (
              ),
              'groups-joined' =>
              array (
                0 => '13',
              ),
              'assignees' =>
              array (
              ),
            ),
          ),
          4 =>
          array (
            'id' => '19',
            'type' => 'users',
            'name' => 'T',
            'links' =>
            array (
              'product' => '3',
              'profile' => '19',
              'message-preference' => '19',
              'chief' => NULL,
              'groups' =>
              array (
              ),
              'groups-joined' =>
              array (
                0 => '13',
              ),
              'assignees' =>
              array (
              ),
            ),
          ),
          5 =>
          array (
            'id' => '20',
            'type' => 'users',
            'name' => 'Api Admin',
            'links' =>
            array (
              'product' => '3',
              'profile' => '20',
              'message-preference' => '20',
              'chief' => NULL,
              'groups' =>
              array (
              ),
              'groups-joined' =>
              array (
                0 => '13',
              ),
              'assignees' =>
              array (
              ),
            ),
          ),
          6 =>
          array (
            'id' => '21',
            'type' => 'users',
            'name' => 'Api Basic',
            'links' =>
            array (
              'product' => '3',
              'profile' => '21',
              'message-preference' => '21',
              'chief' => NULL,
              'groups' =>
              array (
              ),
              'groups-joined' =>
              array (
                0 => '13',
              ),
              'assignees' =>
              array (
              ),
            ),
          ),
        ),
      ),
      'meta' =>
      array (
        'groups' =>
        array (
          'pagination' =>
          array (
            'page' => 1,
            'size' => 2,
            'total' => 6,
          ),
        ),
      ),
    );
    /**
     * @var array
     */
    private static $blendDocument = array (
      'links' =>
      array (
        'users.product' =>
        array (
          'href' => '/api/v2/products/{users.product}',
          'type' => 'products',
        ),
        'users.groups-joined' =>
        array (
          'href' => '/api/v2/users/{users.id}/links/groups-joined',
          'type' => 'groups',
        ),
        'users.followers' =>
        array (
          'href' => '/api/v2/follows?subject={users.id}',
          'type' => 'follows',
        ),
        'products.account' =>
        array (
          'href' => '/api/v2/accounts/{products.account}',
          'type' => 'accounts'
        ),
        'products.groups' =>
        array (
          'href' => '/api/v2/products/{products.id}/links/groups',
          'type' => 'groups'
        ),
        'products.users' =>
        array (
          'href' => '/api/v2/users?product={products.id}',
          'type' => 'users'
        )
      ),
      'users' =>
      array (
        0 =>
        array (
          'id' => '15',
          'type' => 'users',
          'name' => 'John',
          'surname' => 'Connor',
          'email' => NULL,
          'links' =>
          array (
            'product' => '3',
            'groups-joined' =>
            array (
              0 => '13'
            )
          )
        ),
        1 =>
        array (
          'id' => '16',
          'type' => 'users',
          'name' => 'John',
          'surname' => 'Connor',
          'email' => 'johnconnor@gointegro.com',
          'links' =>
          array (
            'product' => '3',
            'groups-joined' =>
            array (
              0 => '13'
            )
          )
        )
      ),
      'products' =>
      array (
        0 =>
        array (
          'id' => '3',
          'type' => 'products',
          'name' => 'Hugo',
          'host' => 'hugo.gointegro.net',
          'links' =>
          array (
            'account' => '3',
            'groups' =>
            array (
              0 => '21',
              1 => '22'
            )
          )
        )
      )
    );

    public function testMergingSimpleDocuments()
    {
        // Given...
        $blender = new Blender;
        // When...
        $blend = $blender->merge(
            self::$usersDocument, self::$productsDocumentVersion1
        );
        // Then...
        $this->assertEquals(self::$blendDocument, $blend);
    }

    /**
     * @expectedException \GoIntegro\Bundle\HateoasBundle\JsonApi\Merge\UnmergeableResourcesException
     */
    public function testMergingSingleAndCollectionDocuments()
    {
        // Given...
        $blender = new Blender;
        // When...
        $blend = $blender->merge(
            self::$productDocumentA, self::$productsDocumentVersion1
        );
        // Then an exception.
    }

    /**
     * @expectedException \GoIntegro\Bundle\HateoasBundle\JsonApi\Merge\UnmergeableResourcesException
     */
    public function testMergingDifferentSingleDocuments()
    {
        // Given...
        $blender = new Blender;
        // When...
        $blend = $blender->merge(
            self::$productDocumentA, self::$productDocumentB
        );
        // Then an exception.
    }

    public function testMergingSameSingleDocuments()
    {
        // Given...
        $blender = new Blender;
        // When...
        $blend = $blender->merge(
            self::$userDocumentAVersion1, self::$userDocumentAVersion2
        );
        // Then...
        $this->assertArrayHasKey('name', $blend['users']);
        $this->assertArrayHasKey('surname', $blend['users']);
        $this->assertArrayHasKey('email', $blend['users']);
    }

    public function testMergingSameCollectionDocuments()
    {
        // Given...
        $blender = new Blender;
        // When...
        $blend = $blender->merge(
            self::$productsDocumentVersion1, self::$productsDocumentVersion2
        );
        // Then...
        $this->assertCount(1, self::$productsDocumentVersion1['products']);
        $this->assertCount(1, self::$productsDocumentVersion2['products']);
        $this->assertCount(2, $blend['products']);
    }

    public function testMergingDocumentsWithMeta()
    {
        // Given...
        $blender = new Blender;
        // When...
        $blend = $blender->merge(
            self::$messagesDocumentWithLinked,
            self::$couponsDocumentVersion1
        );
        // Then...
        $this->assertCount(1, self::$messagesDocumentWithLinked['meta']);
        $this->assertCount(1, self::$couponsDocumentVersion1['meta']);
        $this->assertCount(2, $blend['meta']);
    }

    /**
     * @expectedException \GoIntegro\Bundle\HateoasBundle\JsonApi\Merge\UnmergeableResourcesException
     */
    public function testMergingDocumentsWithSameTypeMeta()
    {
        // Given...
        $blender = new Blender;
        // When...
        $blend = $blender->merge(
            self::$couponsDocumentVersion2,
            self::$couponsDocumentVersion3
        );
        // Then an exception.
    }

    /**
     * @expectedException \LogicException
     * @todo Revisar esto - no sería LogicException si es válida en runtime.
     */
    public function testMergingDocumentsDifferentTopLevelLinks()
    {
        // Given...
        $blender = new Blender;
        // When...
        $blend = $blender->merge(
            self::$couponsDocumentVersion1,
            self::$couponsDocumentVersion2
        );
        // Then an exception.
    }

    public function testMergingDocumentsWithLinkedResources()
    {
        // Given...
        $blender = new Blender;
        // When...
        $blend = $blender->merge(
            self::$messagesDocumentWithLinked,
            self::$groupsDocumentWithLinked
        );
        // Then...
        $this->assertCount(
            1, self::$messagesDocumentWithLinked['linked']
        );
        $this->assertCount(2, self::$groupsDocumentWithLinked['linked']);
        $this->assertCount(3, $blend['linked']);
    }
}
