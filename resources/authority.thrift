namespace php Authority

struct User
{
    1:optional i32                  id
    2:required string               username
    3:optional string               nickname
    4:optional string               password
    5:optional string               email
    6:optional string               telephone
}

struct Group
{
    1:optional i32                  id
    2:required i32                  type
    3:required string               name
    4:optional string               description
}

struct Point
{
    1:optional i32                  id
    2:required string               name
    3:required string               data
    4:optional string               description
}

struct Category
{
    1:optional i32                  id
    2:required string               name
    3:optional string               description
}

struct CategoryPoint
{
    1:i32                           id
    2:string                        name
    3:list<Point>                   children
    4:optional string               description
}

struct CommonRet
{
    1:required i32                  ret
    2:optional string               data
}

struct CategoryRet
{
    1:required i32                  ret
    2:required i32                  total
    3:optional list<Category>       categories
}

struct UserRet
{
    1:required i32                  ret
    2:required i32                  total
    3:optional list<User>           users
}

struct GroupRet
{
    1:required i32                  ret
    2:required i32                  total
    3:optional list<Group>          groups
}

struct UserAuthRet
{
    1:required i32                  ret
    2:optional list<string>         super_points
    3:optional list<string>         points
    4:optional list<Group>          groups
}

struct AssignableGroupRet
{
    1:required i32                  ret
    2:optional list<Group>          groups
}

struct GroupPointRet
{
    1:required i32                  ret
    2:required list<i32>            points
    3:optional Group                parent
}

struct AssignablePointRet
{
    1:required i32                  ret
    2:optional list<CategoryPoint>  points
}

service AuthorityService
{
    UserAuthRet getAuth(1:i32 uid)                                      // 获取用户相关信息

    CommonRet addUser(1:User user)                                      // 新增用户
    CommonRet rmUser(1:i32 user_id)                                     // 删除用户
    CommonRet updateUser(1:i32 user_id, 2:User user)                    // 更新用户信息
    User getUserById(1:i32 user_id)                                     // 根据ID获取单个用户
    User getUserByName(1:string username)                               // 根据用户名获取单个用户
    UserRet getUsers(1:i32 page, 2:i32 pagesize)                        // 获取所有用户，支持分页

    CommonRet addPoint(1:Point point, 2:i32 cate_id)                    // 新增权限点
    CommonRet updatePoint(1:i32 point_id, 2:Point point)                // 更新权限点信息
    CommonRet rmPoint(1:i32 point_id)                                   // 删除权限点

    CommonRet addCategory(1:Category category)                          // 新增权限分类
    CommonRet rmCategory(1:i32 cate_id)                                 // 删除权限分类
    CommonRet updateCategory(1:i32 cate_id, 2:Category category)        // 更新权限分类
    CategoryRet getCategories()                                         // 获取所有权限分类

    CommonRet addGroup(1:Group group, 2:i32 parent)                     // 新增普通组(parent = 0)、权限组
    CommonRet rmGroup(1:i32 group_id)                                   // 删除组
    CommonRet updateGroup(1:i32 group_id, 2:Group group)                // 更新组信息
    GroupRet getGroups(1:i32 type, 2:i32 page, 3:i32 pagesize)          // 获取所有用户组

    CommonRet addRelation(1:i32 parent, 2:i32 child)                    // 新增关系 权限组-普通组，组-权限点，权限分类-权限点
    CommonRet rmRelation(1:i32 parent, 2:i32 child)                     // 删除关系 同上

    AssignableGroupRet getAssignableGroup(1:i32 uid)                    // 获取用户可分配的组
    GroupPointRet getGroupPointById(1:i32 group_id)                     // 获取权限组当前权限点
    AssignablePointRet getAssignablePoint(1:i32 group_id)               // 获取权限组可分配的权限点

    bool assignPoint2Group(1:list<i32> points, 2:i32 group_id)          // 分配权限点给组
    bool assignGroup2User(1:list<i32> group_ids, 2:i32 user_id)         // 分配组给用户
}