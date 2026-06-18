# Changelog

All notable changes to `sawfish-integration` will be documented in this file.

## New Verify Method - 2026-06-18

- Add `verifyClient` method to check for an existing client by matching name, ABN, or BSB + account number
- Register `verifyClient` in the `SawfishIntegration` method map so it is callable via the facade/static API

## v1.2.0 - 2026-06-18

- Add `verifyClient` method to `Clients` resource (`GET /clients/verify`) to check for an existing client by matching name, ABN, or BSB + account number
- Register `verifyClient` in the `SawfishIntegration` method map so it is callable via the facade/static API

## v1.1.0 - 2026-06-11

- Add `Bills` resource with `getBills`, `getBillByUuid`, `getBillsByProviderUuid`, `createBill`, `updateBill`, `voidBill`
- Add `getSupplier` method to `Clients` resource

## v1.0.2 - 2026-05-11

Minor update

- Fixed migration path
- Update texts

## Add client update method - 2025-12-22

[Add updateClient method to Clients resource and register in SawfishIntegration](https://github.com/sprint-digital/sawfish-integration/commit/745d78e154571d9c29a618852e0c4128f4bb1a38)
