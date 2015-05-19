**1. Introduction**

The purpose of the software project is to develop a web application and mobile application with an object oriented programming language such as Java and script language such as PHP, in Windows and MySQL environment, to do mainly the following;

1. Users will enter accessibility violation using mobile application or web site.


2. He enters required information such as location and picture of violation. Other users may enter comment or update violation.

The web application functions and user interface details are given in the following sections of this document.

**1.1. Main Categories**

> We have two important types that we use in the project mostly; disability type and accessibility violation types. Below we listed important types;


1-      Disability Categories

> a. People with Prothesis

> b. Visual Defect

> c. People with Walking Stick

> d. People with Weelchair

2-      Accessibility Violation Categories

> a. Pavement Platform

> b. Architectural Barriers

> c. Grid & Blank Spaces

> d. Car Park Spaces

> e. City Furnitures

> f. Building Access

> g. Building Elements

> h. Enlightening

> i. Color Compliance

> j. Key & Plugins


**2. Requirements**

**2.1. Requirement 1 Function 1. Main User Interface Functions**

1- User Form

> a. Disability Type

> b. Age

> c. Gender

> d. Registration (Mobile + Web)

2- New Accessibility Violation

> a. Current Position (Mobile)

> b. City, Province, Town

3- Disability

> a. Type

> b. Altitude

> c. Longitude

> d. İmage

> e. Description

> f. Ratings

> g. Result

4- Listings

> a. Current Position (Mobile + Web)

> b. User Label List (Mobile)

5- Comments

**2.2. Requirement 2 Function 2. Admin Page Main User Interface and Functions**


Admin Page Operations are listed below:

1-	Insert/Update Category

> a.	Insert/Update Disability

> b.	Insert/Update Accessibility Violation

2-	Retrieve Report

> a.	Statistical reports according to

> i.	Accessibility Violation type

> ii.	Disability

> iii.	Location

3-	Update User

> a.	Password issues

> b.	User type update

4-	Insert or Update User types
> a.	Enter new user type

> i.	Admin

> ii.	Normal User

5-	Update Authorization of Users

> a.	Ban or Activate users

> b.	Make admin some users

6-	List and Update Entries

> a.	Update or delete entries


All functions above are links to related web pages.


**2.3. Requirement 3. Function 3: Insert Category**

Admin can enter new category according to disability or obstacle. First he chooses whether new category belongs to disability or obstacle then write the name of the category into the textbox.  If picture or note/definition available, uploads them.

•	Category type (required)

•	Category name (required)

•	Accessibility violation picture

•	Accessibility violation definition


**2.4. Requirement 4. Function 4: Update Category**


Categories can be updated after entered. Pictures or definitions can be added or wrong category can be deleted.

•	Category type (comes filled)

•	Category name (comes filled)

•	Accessibility violation picture

•	Accessibility violation definition

**2.5. Requirement 5. Function 5: Retrieve Report**

Report page helps admins to report database. It should have following filters:


-	Accessibility Violation Type

-	Disability (people)

-	Location

> o	Country

> o	City

> o	District

> o	Street

-	Solved or Unsolved


It should return the number of violations according to filter. This report can be downloaded as excel. Moreover when user clicks the number it should show the violations or users. For example if you are looking for active violations in Hisarustu for blind people you should filter like this:


-	Visually impaired

-	Turkey / Sarıyer / Hisarustu

-	Unsolved


Then it will return a number for instance 5. This number is also a link and redirects you to a page where you can view the violations.


**2.6. Requirement 6. Function 6: Insert or Update User**


Admin can search for user and update his information such as:


-	Username

-	Password

-	Mail address

-	User type

-	Active


Or he can enter new user type filling the information below:


-	Username

-	Password

-	Mail address

-	User type

-	Active  (Banned or Active)


**2.7. Requirement 7. Function 7: Insert or Update User Types**


At the beginning we have two user types:


-	Admin

-	Users

> o	Registered

> o	Not registered

-	Editors

> o	Checks entries


In the long time we may need to add new user types or update user types. For example we may need to add more admin to check comments or violations written or inserted. Or we may need to add new user type such as authorized user, a municipality employee.


When admin wants to update user type, he can change the name and authority of the user.


**2.8. Requirement 8. Function 8: Update Authorization of User Types / Users**



In this part, admin can update authorizations for users. Authorizations are listed below:


•	Insert new user

•	Update user

•	Delete user

•	Insert category

•	Delete category

•	Update category

•	Insert user type

•	Delete user type

•	Update user type

•	Insert violations

•	Update violations

•	Delete violations

•	Insert comment

•	Update comment

•	Delete comment

•	Report violation

•	Report comment

•	Ban user

•	Activate user

•	List user

•	Search user

•	List report

•	Download report


For each user types all functions can be active or not. Admin will determine it. For example user cannot update or delete comment but editors can.


**2.9. Requirement 9. Function 9: List and Update Entries**


Admin or editors may need to remove violations or comments. Some users may enter meaningless or wrong violations and some users may write comments including slangs. These entries should be deleted immediately. That’s why editors should list reported violations or comments. Then delete it and determine if user needs to be banned.
Admin or editor can search for specific violations using filters according to username or location or both.


•	Username

•	Location


Moreover, they can list reported violations and comments. This list will be shown separately. Editors must read all reported incidents and decide the next operation. Next operations listed below:


•	Delete violation and ban user

•	Delete violation and do not ban user

•	Delete comment and ban user

•	Delete comment and do not ban user

•	Update violation

•	Update comment


**2.10. Requirement 10. Function 10: Reporting** (Bunun yerini değiştirsek iyi olur başlarda bir yer daha iyi gibi en azından adminden once olmalı)


Users can report violation count and what type of violations exists in the selected district. When user clicks “report” button, a form comes up. It asks location and disability type. User should enter at least the city name. He can also enter more specific address.


•	Accessibility violation type

•	Disability

•	Location

> o	City

> o	District

> o	Street

•	Severity

•	Solved / Unsolved


Only required info is city. If user do not enter disability type or disability, then system will give numbers for each disability type or disable person type exists in the system.
Example report for Istanbul (only city selected):


| **Disability Type** | **Accessibility Violation Type** | **Severity** | **Solved Count** |
|:--------------------|:---------------------------------|:-------------|:-----------------|
| Visually Impaired   | Traffic Light                    | Minor        | 3                |
| Visually Impaired   | Pavement                         | Critical     | 2                |
| Visually Impaired   | Traffic Light                    | Critical     | 1                |




**2.11. Requirement 11. Function 11: Adding Comment**


Anonymous users (guests)  and  users should be able to add comments about  accessibility violations.

**2.12. Requirement 12. Function 12: Report Abused Comment**

The system should allow users to report abused comments with “Report Inconvenent Comment”  button.  The users who entered these comment should be banned by the system.

**2.13. Requirement 13. Function 13: Follow Violations**


The System should allow users and guests to follow the violations. The user who is following a violation should be informed about the actions related with the violation.

**2.14. Requirement 14. Function 14: Witnessing Violation / Resolution of Violation**

The user shoul d be able to express that she has witnessed the violation or resolution of the violation.

**2.15. Requirement 15. Function 15: Resolving Violation**


The violation should be marked as resolved by any user when it is solved.  The user must  add a photo of the violation to mark it as solved.


**2.16. Requirement 16. Function 16: Defining Instance**


The attributes defining an instance are listed below:

1-	Instance Type

> a.	Disability Type : Each instance will contain at least one type of disability that it will affect. For instances that will affect all people regardless of their disability or whether they possess a disability; there will be an option named "All". The disability type list will open for viewing and editing for all users.

> b.	Accessibility Violation Type (Architecture): Each instance will contain at least one type of obstacle. The obstacle type list will open for viewing and editing for all users.

2-	Altitude/Longitude: Each instance will contain a location defined by the altitude and longitude of the instance. The obstacle type will be open for viewing for all users. It can be edited by administrators.

3-	Picture: All users will be enabled to add picture(s) to the instance. All users will be enabled to view the added pictures. Administrators will be enabled to remove all the pictures. Registered users will be enabled to remove the pictures they added.

4-	Comments: All users will be enabled to ad comment(s) to the instance. All users will be enabled to view the comments. Administrators will be enabled to delete comments. Registered users will be enabled to edit or delete the comments they added.

5-	Description: Each instance.The user who inserted the instance will be enabled to add a description to the instance. Administrators will be enabled to edit the description of the instance. Registered users will be enabled to edit the description of the instances that they have inserted. All users will be able to view the description.

6-	Severity Rating: There will be a online rating system based on user votes rating the severity of the instance. The rating result will fall under the following three rates:

> a.	Mild
> b.	Severe
> c.	Very severe

7-	Status: Each instance can have one of the following 3 statūs:

> a.	Unresolved: All instances will have this status upon insertion until the solution or invalidation of the instance.
> b.	Resolved: The instances that have been solved (i.e. all the associated disabilities are removed) will have this status.


**2.17. Requirement 17. Function 17: Listing**


The instances will be listed and displayed on a map or a list or both (according to user preference).

Any active (unremoved)instance can be selected and its details can be viewed.

The instances displayed on the map will be colored according to obstacle categories.

The listing criterias are listed below:

1- Disability Category: Users will be able to select certain type(s) of disabilities that the listed instances will affect.

2- Accessibility Violation Category: Users able to select certain type(s) of obtacles that the listed instances will possess.

3- Location:
> a- Current Position: Mobile users will be able to search the instances within a selected physical proximity to the current position that is obtained from the geolocation of the mobile device.
> b- Selected posisition: User will be able to search the instances within a selected physical proximity to a selected position. The selection of the position will be made via 2 options:
> > i- By selecting city, district, street.
> > ii- By pinpointing from map.

4- Label: The registered users will be able to list the instrances that they have labeled.

**2.18. Requirement 18. Function 18: Editing Instance**


The registered users will be able to view and edit the following attributes of the instances that they insterted:
1- Disability Type
2- Obstacle Type
3- Description

**2.19. Requirement 19. Function 19: Removing Instance**


The registered users will be able access to remove the instances that they insterted.

**2.20. Requirement 20. Development Environment**


We have two development environments. For web PHP programming language will be used, for mobile we will use Java.

Web:


-	IDE:

-	Programming language: PHP

-	Operation System: Windows


Mobile:

-	IDE: Eclipse

-	Programming language: Java

-	Operation System: Android (min version 2.3)

-	Google maps API for location determination


**2.21. Requirement 21. Security**


All client-server operations through the Internet will be through SSL to provide security. On mobile security is not our first concern. Our admins and editors will check reported violations and comments.


**2.22. Requirement 22. Logging**


Actions of all users such as violation entry, login, logout, registration, comment will be logged. Moreover, admin or other user types operations will also logged for security reasons.


**3. Testing**


This part will be revised later.