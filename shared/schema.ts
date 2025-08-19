import { sql } from 'drizzle-orm';
import { relations } from 'drizzle-orm';
import {
  index,
  jsonb,
  pgTable,
  timestamp,
  varchar,
  text,
  decimal,
  integer,
  boolean,
  pgEnum,
  unique,
} from "drizzle-orm/pg-core";
import { createInsertSchema } from "drizzle-zod";
import { z } from "zod";

// Session storage table (Required for Replit Auth)
export const sessions = pgTable(
  "sessions",
  {
    sid: varchar("sid").primaryKey(),
    sess: jsonb("sess").notNull(),
    expire: timestamp("expire").notNull(),
  },
  (table) => [index("IDX_session_expire").on(table.expire)],
);

// User storage table (Required for Replit Auth)
export const users = pgTable("users", {
  id: varchar("id").primaryKey().default(sql`gen_random_uuid()`),
  email: varchar("email").unique(),
  firstName: varchar("first_name"),
  lastName: varchar("last_name"),
  profileImageUrl: varchar("profile_image_url"),
  phone: varchar("phone"),
  userType: varchar("user_type").notNull().default('individual'), // individual, corporate, government, merchant, agent, admin
  createdAt: timestamp("created_at").defaultNow(),
  updatedAt: timestamp("updated_at").defaultNow(),
});

// Companies table for corporate users
export const companies = pgTable("companies", {
  id: varchar("id").primaryKey().default(sql`gen_random_uuid()`),
  name: varchar("name").notNull(),
  registrationNumber: varchar("registration_number"),
  address: text("address"),
  contactEmail: varchar("contact_email"),
  contactPhone: varchar("contact_phone"),
  createdAt: timestamp("created_at").defaultNow(),
  updatedAt: timestamp("updated_at").defaultNow(),
});

// Fleet managers - linking users to companies
export const fleetManagers = pgTable("fleet_managers", {
  id: varchar("id").primaryKey().default(sql`gen_random_uuid()`),
  userId: varchar("user_id").notNull().references(() => users.id),
  companyId: varchar("company_id").notNull().references(() => companies.id),
  role: varchar("role").notNull().default('manager'), // manager, admin
  createdAt: timestamp("created_at").defaultNow(),
}, (table) => [
  unique().on(table.userId, table.companyId)
]);

// Drivers table
export const drivers = pgTable("drivers", {
  id: varchar("id").primaryKey().default(sql`gen_random_uuid()`),
  userId: varchar("user_id").notNull().references(() => users.id),
  companyId: varchar("company_id").references(() => companies.id),
  licenseNumber: varchar("license_number"),
  licenseExpiry: timestamp("license_expiry"),
  status: varchar("status").notNull().default('active'), // active, suspended, pending
  createdAt: timestamp("created_at").defaultNow(),
  updatedAt: timestamp("updated_at").defaultNow(),
});

// Vehicle status enum
export const vehicleStatusEnum = pgEnum('vehicle_status', ['pending', 'approved', 'rejected', 'active', 'suspended']);

// Vehicles table
export const vehicles = pgTable("vehicles", {
  id: varchar("id").primaryKey().default(sql`gen_random_uuid()`),
  registrationNumber: varchar("registration_number").notNull().unique(),
  ownerId: varchar("owner_id").notNull().references(() => users.id),
  companyId: varchar("company_id").references(() => companies.id),
  vehicleType: varchar("vehicle_type").notNull(), // sedan, suv, truck, van, bus
  make: varchar("make"),
  model: varchar("model"),
  year: integer("year"),
  fuelType: varchar("fuel_type").notNull(), // petrol, diesel, hybrid
  status: vehicleStatusEnum("status").notNull().default('pending'),
  approvedAt: timestamp("approved_at"),
  approvedBy: varchar("approved_by").references(() => users.id),
  rejectionReason: text("rejection_reason"),
  createdAt: timestamp("created_at").defaultNow(),
  updatedAt: timestamp("updated_at").defaultNow(),
});

// Documents table for vehicle registration books
export const documents = pgTable("documents", {
  id: varchar("id").primaryKey().default(sql`gen_random_uuid()`),
  vehicleId: varchar("vehicle_id").notNull().references(() => vehicles.id),
  documentType: varchar("document_type").notNull(), // registration_book, license, insurance
  fileName: varchar("file_name").notNull(),
  filePath: varchar("file_path").notNull(),
  fileSize: integer("file_size"),
  mimeType: varchar("mime_type"),
  uploadedAt: timestamp("uploaded_at").defaultNow(),
});

// Fuel balances table - separate balances per fuel type
export const fuelBalances = pgTable("fuel_balances", {
  id: varchar("id").primaryKey().default(sql`gen_random_uuid()`),
  userId: varchar("user_id").notNull().references(() => users.id),
  fuelType: varchar("fuel_type").notNull(), // petrol, diesel
  balance: decimal("balance", { precision: 10, scale: 2 }).notNull().default('0.00'),
  createdAt: timestamp("created_at").defaultNow(),
  updatedAt: timestamp("updated_at").defaultNow(),
}, (table) => [
  unique().on(table.userId, table.fuelType)
]);

// Transaction limits table
export const transactionLimits = pgTable("transaction_limits", {
  id: varchar("id").primaryKey().default(sql`gen_random_uuid()`),
  userId: varchar("user_id").notNull().references(() => users.id),
  userType: varchar("user_type").notNull(), // individual, corporate, government
  dailyPurchaseLimit: decimal("daily_purchase_limit", { precision: 10, scale: 2 }).notNull().default('100.00'),
  monthlyPurchaseLimit: decimal("monthly_purchase_limit", { precision: 10, scale: 2 }).notNull().default('2500.00'),
  dailyTransferLimit: decimal("daily_transfer_limit", { precision: 10, scale: 2 }).notNull().default('50.00'),
  createdAt: timestamp("created_at").defaultNow(),
  updatedAt: timestamp("updated_at").defaultNow(),
}, (table) => [
  unique().on(table.userId)
]);

// Coupons table
export const coupons = pgTable("coupons", {
  id: varchar("id").primaryKey().default(sql`gen_random_uuid()`),
  code: varchar("code").notNull().unique(),
  fuelType: varchar("fuel_type").notNull(), // petrol, diesel
  amount: decimal("amount", { precision: 10, scale: 2 }).notNull(),
  status: varchar("status").notNull().default('active'), // active, used, expired, deactivated
  description: text("description"),
  expiryDate: timestamp("expiry_date"),
  usedAt: timestamp("used_at"),
  usedBy: varchar("used_by").references(() => users.id),
  createdBy: varchar("created_by").notNull().references(() => users.id),
  createdAt: timestamp("created_at").defaultNow(),
});

// Merchants table
export const merchants = pgTable("merchants", {
  id: varchar("id").primaryKey().default(sql`gen_random_uuid()`),
  userId: varchar("user_id").notNull().references(() => users.id),
  stationName: varchar("station_name").notNull(),
  address: text("address"),
  contactPhone: varchar("contact_phone"),
  bankName: varchar("bank_name"),
  accountNumber: varchar("account_number"),
  accountHolder: varchar("account_holder"),
  branchCode: varchar("branch_code"),
  pendingBalance: decimal("pending_balance", { precision: 10, scale: 2 }).notNull().default('0.00'),
  status: varchar("status").notNull().default('active'), // active, suspended, pending
  createdAt: timestamp("created_at").defaultNow(),
  updatedAt: timestamp("updated_at").defaultNow(),
});

// Merchant employees table
export const merchantEmployees = pgTable("merchant_employees", {
  id: varchar("id").primaryKey().default(sql`gen_random_uuid()`),
  merchantId: varchar("merchant_id").notNull().references(() => merchants.id),
  name: varchar("name").notNull(),
  employeeId: varchar("employee_id").notNull(),
  pin: varchar("pin").notNull(),
  status: varchar("status").notNull().default('active'), // active, suspended
  createdAt: timestamp("created_at").defaultNow(),
  updatedAt: timestamp("updated_at").defaultNow(),
}, (table) => [
  unique().on(table.merchantId, table.employeeId)
]);

// Transactions table
export const transactions = pgTable("transactions", {
  id: varchar("id").primaryKey().default(sql`gen_random_uuid()`),
  userId: varchar("user_id").notNull().references(() => users.id),
  vehicleId: varchar("vehicle_id").references(() => vehicles.id),
  merchantId: varchar("merchant_id").references(() => merchants.id),
  employeeId: varchar("employee_id").references(() => merchantEmployees.id),
  transactionType: varchar("transaction_type").notNull(), // fuel_purchase, fuel_usage, fuel_transfer, top_up, coupon_redemption
  fuelType: varchar("fuel_type"), // petrol, diesel
  amount: decimal("amount", { precision: 10, scale: 2 }).notNull(),
  monetaryValue: decimal("monetary_value", { precision: 10, scale: 2 }),
  status: varchar("status").notNull().default('pending'), // pending, completed, failed, cancelled
  paymentMethod: varchar("payment_method"), // paynow_ecocash, paynow_telecash, paynow_visa, payfast
  reference: varchar("reference"),
  notes: text("notes"),
  createdAt: timestamp("created_at").defaultNow(),
  completedAt: timestamp("completed_at"),
});

// Withdrawal requests table
export const withdrawalRequests = pgTable("withdrawal_requests", {
  id: varchar("id").primaryKey().default(sql`gen_random_uuid()`),
  merchantId: varchar("merchant_id").notNull().references(() => merchants.id),
  amount: decimal("amount", { precision: 10, scale: 2 }).notNull(),
  status: varchar("status").notNull().default('pending'), // pending, approved, completed, rejected
  requestedAt: timestamp("requested_at").defaultNow(),
  processedAt: timestamp("processed_at"),
  processedBy: varchar("processed_by").references(() => users.id),
  notes: text("notes"),
});

// Admin roles and permissions
export const adminRoles = pgTable("admin_roles", {
  id: varchar("id").primaryKey().default(sql`gen_random_uuid()`),
  userId: varchar("user_id").notNull().references(() => users.id),
  role: varchar("role").notNull(), // super_admin, admin, moderator
  permissions: jsonb("permissions").notNull().default('{}'),
  createdAt: timestamp("created_at").defaultNow(),
  updatedAt: timestamp("updated_at").defaultNow(),
}, (table) => [
  unique().on(table.userId)
]);

// Relations
export const usersRelations = relations(users, ({ many, one }) => ({
  fuelBalances: many(fuelBalances),
  vehicles: many(vehicles),
  transactions: many(transactions),
  transactionLimits: one(transactionLimits),
  fleetManager: one(fleetManagers),
  driver: one(drivers),
  merchant: one(merchants),
  adminRole: one(adminRoles),
}));

export const companiesRelations = relations(companies, ({ many }) => ({
  fleetManagers: many(fleetManagers),
  vehicles: many(vehicles),
  drivers: many(drivers),
}));

export const vehiclesRelations = relations(vehicles, ({ one, many }) => ({
  owner: one(users, { fields: [vehicles.ownerId], references: [users.id] }),
  company: one(companies, { fields: [vehicles.companyId], references: [companies.id] }),
  documents: many(documents),
  transactions: many(transactions),
}));

export const transactionsRelations = relations(transactions, ({ one }) => ({
  user: one(users, { fields: [transactions.userId], references: [users.id] }),
  vehicle: one(vehicles, { fields: [transactions.vehicleId], references: [vehicles.id] }),
  merchant: one(merchants, { fields: [transactions.merchantId], references: [merchants.id] }),
  employee: one(merchantEmployees, { fields: [transactions.employeeId], references: [merchantEmployees.id] }),
}));

export const couponsRelations = relations(coupons, ({ one }) => ({
  creator: one(users, { fields: [coupons.createdBy], references: [users.id] }),
  user: one(users, { fields: [coupons.usedBy], references: [users.id] }),
}));

// Insert schemas
export const insertUserSchema = createInsertSchema(users).omit({ id: true, createdAt: true, updatedAt: true });
export const insertCompanySchema = createInsertSchema(companies).omit({ id: true, createdAt: true, updatedAt: true });
export const insertVehicleSchema = createInsertSchema(vehicles).omit({ id: true, createdAt: true, updatedAt: true, approvedAt: true, approvedBy: true });
export const insertCouponSchema = createInsertSchema(coupons).omit({ id: true, createdAt: true, usedAt: true, usedBy: true, code: true, createdBy: true });
export const insertTransactionSchema = createInsertSchema(transactions).omit({ id: true, createdAt: true, completedAt: true });
export const insertMerchantSchema = createInsertSchema(merchants).omit({ id: true, createdAt: true, updatedAt: true });

// Types
export type UpsertUser = typeof users.$inferInsert;
export type User = typeof users.$inferSelect;
export type Company = typeof companies.$inferSelect;
export type Vehicle = typeof vehicles.$inferSelect;
export type InsertVehicle = z.infer<typeof insertVehicleSchema>;
export type FuelBalance = typeof fuelBalances.$inferSelect;
export type Transaction = typeof transactions.$inferSelect;
export type InsertTransaction = z.infer<typeof insertTransactionSchema>;
export type Coupon = typeof coupons.$inferSelect;
export type InsertCoupon = z.infer<typeof insertCouponSchema>;
export type Merchant = typeof merchants.$inferSelect;
export type InsertMerchant = z.infer<typeof insertMerchantSchema>;
export type WithdrawalRequest = typeof withdrawalRequests.$inferSelect;
export type AdminRole = typeof adminRoles.$inferSelect;
export type TransactionLimit = typeof transactionLimits.$inferSelect;
