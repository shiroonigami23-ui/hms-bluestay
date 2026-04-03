USE hotel_management;

INSERT INTO users (full_name,email,phone,role,password_hash) VALUES
('System Owner','owner@bluestay.local','9990000001','owner','$2y$10$gJzG.3zAzugbDHyQzS3j3ONKpIpN/P4CyXTNXvLEqRQE8Mr0uroCS'),
('Hotel Admin','admin@bluestay.local','9990000002','admin','$2y$10$gJzG.3zAzugbDHyQzS3j3ONKpIpN/P4CyXTNXvLEqRQE8Mr0uroCS'),
('Front Desk','reception@bluestay.local','9990000003','reception','$2y$10$gJzG.3zAzugbDHyQzS3j3ONKpIpN/P4CyXTNXvLEqRQE8Mr0uroCS'),
('Housekeeper One','housekeeping@bluestay.local','9990000004','housekeeping','$2y$10$gJzG.3zAzugbDHyQzS3j3ONKpIpN/P4CyXTNXvLEqRQE8Mr0uroCS'),
('Guest Demo','guest@bluestay.local','9990000005','customer','$2y$10$gJzG.3zAzugbDHyQzS3j3ONKpIpN/P4CyXTNXvLEqRQE8Mr0uroCS');

INSERT INTO rooms (room_number,floor_no,room_type,status,base_rate) VALUES
('101',1,'Standard','available',2500),
('102',1,'Deluxe','occupied',3500),
('201',2,'Suite','available',5500),
('202',2,'Deluxe','dirty',3600),
('301',3,'Family','maintenance',4800);

INSERT INTO bookings (booking_code,guest_user_id,room_id,check_in,check_out,adults,children,source,status) VALUES
('BK2604031001',5,2,'2026-04-03','2026-04-05',2,0,'Direct','checked_in'),
('BK2604031002',5,3,'2026-04-06','2026-04-08',2,1,'OTA','confirmed');

INSERT INTO housekeeping_tasks (room_id,assigned_to_user_id,task_type,priority,status) VALUES
(4,4,'Deep Clean','high','pending'),
(2,4,'Linen Refill','medium','in_progress');

INSERT INTO service_requests (booking_id,room_id,guest_user_id,request_type,description,priority,status) VALUES
(1,2,5,'room_service','Need extra towels and drinking water','medium','open'),
(1,2,5,'laundry','Pickup laundry bag from room','low','in_progress');

INSERT INTO invoices (invoice_no,booking_id,gstin,sub_total,tax_total,total_amount,payment_status) VALUES
('INV-260403-001',1,'27ABCDE1234F1Z5',5000,900,5900,'partial'),
('INV-260403-002',2,'27ABCDE1234F1Z5',11000,1980,12980,'unpaid');

INSERT INTO payments (invoice_id,method,amount,transaction_ref,payment_status,paid_at) VALUES
(1,'upi',3000,'UPIREF12345','success',NOW());

INSERT INTO inventory_items (item_name,category,unit,stock_qty,reorder_level,cost_price) VALUES
('Bath Towel','Housekeeping','pcs',120,40,280),
('Drinking Water Bottle','Room Service','pcs',300,100,18),
('Rice (Premium)','Kitchen','kg',85,30,65);

INSERT INTO menu_items (item_name,category,price,is_available) VALUES
('Veg Sandwich','Snacks',180,1),
('Paneer Butter Masala','Main Course',320,1),
('Fresh Lime Soda','Beverage',120,1);

INSERT INTO visitor_logs (visitor_name,phone,vehicle_no,purpose,check_in_at,logged_by_user_id) VALUES
('Ravi Sharma','9876543210','UP32AB1234','Meeting Guest','2026-04-03 11:25:00',3),
('Courier Staff','9000012345',NULL,'Package Delivery','2026-04-03 15:10:00',3);

-- Demo login password for all users above: Password@123
