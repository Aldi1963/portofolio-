import { NextResponse } from "next/server";

interface ContactPayload {
  name?: unknown;
  email?: unknown;
  subject?: unknown;
  message?: unknown;
}

const EMAIL_RE = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

export async function POST(req: Request) {
  let body: ContactPayload;
  try {
    body = (await req.json()) as ContactPayload;
  } catch {
    return NextResponse.json({ error: "Format request tidak valid." }, { status: 400 });
  }

  const name = typeof body.name === "string" ? body.name.trim() : "";
  const email = typeof body.email === "string" ? body.email.trim() : "";
  const subject = typeof body.subject === "string" ? body.subject.trim() : "";
  const message = typeof body.message === "string" ? body.message.trim() : "";

  if (!name || !email || !subject || !message) {
    return NextResponse.json({ error: "Semua field wajib diisi." }, { status: 400 });
  }
  if (!EMAIL_RE.test(email)) {
    return NextResponse.json({ error: "Format email tidak valid." }, { status: 400 });
  }
  if (message.length < 10) {
    return NextResponse.json({ error: "Pesan minimal 10 karakter." }, { status: 400 });
  }
  if (message.length > 5000) {
    return NextResponse.json({ error: "Pesan terlalu panjang." }, { status: 400 });
  }

  // Integrasi ke layanan email (Resend, SendGrid, dll.) bisa ditambah di sini.
  // Sementara kita log saja agar form bisa di-test secara lokal.
  console.info("[contact] new message:", { name, email, subject });

  return NextResponse.json({ ok: true });
}
