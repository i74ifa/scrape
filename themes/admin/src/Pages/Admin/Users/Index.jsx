import AdminLayout from "../../../Layouts/AdminLayout";
import {
    Table,
    TableContent,
    TableHeader,
    TableColumn,
    TableBody,
    TableRow,
    TableCell,
    Chip,
    Card,
    CardContent,
} from "@heroui/react";

export default function Index({ users }) {
    return (
        <AdminLayout title="المستخدمين">
            <div className="flex flex-col gap-8 pb-10">
                <div className="px-2">
                    <h2 className="text-xl font-bold text-zinc-500 dark:text-zinc-400">
                        عرض {users.length} مستخدم
                    </h2>
                </div>

                <Card className="bg-white/80 dark:bg-zinc-900/80 backdrop-blur-xl rounded-[2rem] overflow-hidden">
                    <CardContent className="p-0">
                        <Table className="w-full">
                            <TableContent aria-label="Users Table">
                                <TableHeader className="text-right">
                                    <TableColumn
                                        className="rtl:text-right"
                                        isRowHeader
                                    >
                                        الاسم
                                    </TableColumn>
                                    <TableColumn className="rtl:text-right">
                                        البريد الإلكتروني
                                    </TableColumn>
                                    <TableColumn className="rtl:text-right">
                                        الدور
                                    </TableColumn>
                                    <TableColumn className="rtl:text-right">
                                        تاريخ الاضافة
                                    </TableColumn>
                                </TableHeader>
                                <TableBody
                                    items={users}
                                    renderEmptyState={() => (
                                        <p className="text-center py-10 text-zinc-400">
                                            No users found.
                                        </p>
                                    )}
                                >
                                    {(item) => (
                                        <TableRow
                                            id={item.id}
                                            className="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors"
                                        >
                                            <TableCell>
                                                <p className="font-semibold text-sm">
                                                    {item.name}
                                                </p>
                                            </TableCell>
                                            <TableCell>
                                                <p className="text-sm text-zinc-500">
                                                    {item.email}
                                                </p>
                                            </TableCell>
                                            <TableCell>
                                                <Chip
                                                    size="sm"
                                                    variant="flat"
                                                    color="default"
                                                >
                                                    {item.role || "user"}
                                                </Chip>
                                            </TableCell>
                                            <TableCell>
                                                <p className="text-sm text-zinc-400">
                                                    {item.created_at
                                                        ? new Date(
                                                              item.created_at,
                                                          ).toLocaleDateString()
                                                        : "—"}
                                                </p>
                                            </TableCell>
                                        </TableRow>
                                    )}
                                </TableBody>
                            </TableContent>
                        </Table>
                    </CardContent>
                </Card>
            </div>
        </AdminLayout>
    );
}
