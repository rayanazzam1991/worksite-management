<?php

use App\Models\Address;
use App\Models\User;
use App\Models\WorkSite;

describe('Warehouse Movements', function () {
    beforeEach(function () {

        $this->workSite = WorkSite::factory()->create();
        $this->admin = User::factory()->admin()->create();
        $this->notAdmin = User::factory()->worker()->create();
        $this->address = Address::factory()->create();

    });

    //    it('should return error while moving a quantity bigger than already exists in the warehouse', function() {});
    //    test('item quantity balance while moving items between warehouses', function() {});
    //    it('should be adding items between supplier and warehouse', function() {});
    //    it('should be dropping items between warehouse and worksite', function() {});

});

//    it('should have the option to attach a wareHouse with a workSite or make it as main workHouse
//    without a workSite', function () {
//    });
//    it('should assign store keeper to a wareHouse, and at least one for each', function () {
//    });
//    it('should be able to move items between warehouses and make data consist between them', function () {
//    });
//    it('should be able to add new items to a warehouse from any external supplier', function () {
//    });
//    it('should track all movements between warehouses', function () {
//    });
//    it('should move items to a workSite from its own warehouse only', function () {
//    });
//    it('should track all items movements between worksite and its warehouse', function () {
//    });
//
//    test('if items entered to a worksite are the same the drop off its wareHouse', function () {
//    });
//});
