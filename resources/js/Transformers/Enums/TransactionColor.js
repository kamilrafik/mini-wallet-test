import {TransactionSign} from "@/Dtos/Enums/TransactionSign.js";

export const TransactionColor = Object.freeze({
    '': 'text-gray-600',
    [TransactionSign.PLUS]: 'text-green-600',
    [TransactionSign.MINUS]: 'text-red-600'
})
