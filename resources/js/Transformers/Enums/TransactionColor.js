import {TransactionSign} from "@/Dtos/Enums/TransactionSign.js";

export const TransactionColor = Object.freeze({
    '': '#374151',
    [TransactionSign.PLUS]: '#16a34a',
    [TransactionSign.MINUS]: '#dc2626'
})
