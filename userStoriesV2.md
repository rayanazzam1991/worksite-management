<h2>Worksite management system</h2>

<h3>Worksite Management Module:</h3>
<ul>
  <li>Worksite entity fields check → it should have not nullable fields</li>
  <li>WorkSite routes check → it should have all routes for /workSite</li>

  <li>Create WorkSite → As a non-authenticated, I cant create a main workSite</li>
  <li>Create WorkSite → As not admin, I cant create a main workSite</li>
  <li>Create WorkSite → As an administrator, I want to create a main workSite</li>
  <li>Create WorkSite → As an administrator, I want to create a sub worksites</li>
  <li>Create WorkSite → As an administrator, should return validation error when no data</li>

  <li>Update WorkSite → As a non-authenticated, I cant update a main workSite</li>
  <li>Update WorkSite → As not admin, I cant update a main workSite</li>
  <li>Update WorkSite → As an administrator, I want to update workSite main info</li>
  <li>Update WorkSite → As an administrator, I want to update workSite contractor before workSite finished → This test did not perform any assertions</li>

  <li>List WorkSites → As a non-authenticated, I cant show list of worksites</li>
  <li>List WorkSites → As not admin, I cant show list of worksites</li>
  <li>List WorkSites → As an admin, I can show list of worksites</li>
  <li>List WorkSites → As an admin, I can show list of worksites without customer and category while creating</li>

  <li>Show WorkSites Details → As a non-authenticated, I cant show details of a workSite</li>
  <li>Show WorkSites Details → As not admin, I cant show details of a workSite</li>
  <li>Show WorkSites Details → it should return not found error if workSite not existed in database</li>
  <li>Show WorkSites Details → As an admin, I can show details of a workSite</li>
  <li>Show WorkSites Details → As an admin, I can show details of a workSite with payments and items</li>

  <li>Close WorkSites → As a non-authenticated, I cant close a workSite</li>
  <li>Close WorkSites → As not admin, I cant close a workSite</li>
  <li>Close WorkSites → it should return not found error if workSite not existed in database</li>
  <li>Close WorkSites → it should prevent me closing workSite with active worksites</li>
  <li>Close WorkSites → it should prevent me closing workSite with unpaid payments</li>
  <li>Close WorkSites → As an admin, I can close a workSite with full payments and closed sub worksites</li>

  <li>Assign Contractor to WorkSites → As a non-authenticated, I cant assign contractor to a workSite</li>
  <li>Assign Contractor to WorkSites → As not admin, I cant assign contractor to a workSite</li>
  <li>Assign Contractor to WorkSites → it should return not found error if workSite not existed in database and if contractor not existed</li>
  <li>Assign Contractor to WorkSites → it should add contractor of a workSite</li>
  <li>Assign Contractor to WorkSites → it should update contractor of a workSite</li>
  <li>Assign Contractor to WorkSites → As an admin i can remove contractor of a workSite</li>
</ul>

<h3>Worksite Support Module:</h3>
<ul>
<li>
As an administrator, I should manage categories
of workSite.
</li>
<li>
As an administrator, I should manage customers of workSite.
</li>
<li>
As an administrator, I should manage items and theirs categories of workSite.
</li>
<li>
As an administrator, I should manage workers of workSite.
</li>
</ul>
<h3>Admin Module:</h3>
<ul>
<li>
As an administrator, I should manage categories.
</li>
<li>
As an administrator, I should manage customers.
</li>
<li>
As an administrator, I should manage items and theirs categories.
</li>
<li>
As an administrator, I should manage all payments.
</li>
<li>
As an administrator, I should manage all workers.
</li>
</ul>
